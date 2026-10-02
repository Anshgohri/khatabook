<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelSyncService
{
    /**
     * Storage path of the Excel file (relative to the disk root).
     */
    private const STORAGE_PATH = 'exports/customer_sales_records.xlsx';

    /**
     * Temp file used when writing via non-local (S3) disks.
     */
    private const TMP_PATH = '/tmp/customer_sales_records.xlsx';

    private const HEADERS = [
        'Sale ID',
        'Date',
        'Customer Name',
        'Phone',
        'City',
        'Items Sold',
        'Quantity',
        'Unit Price (₹)',
        'Discount (₹)',
        'Total Amount (₹)',
        'Payment Status',
        'Notes',
        'Recorded By',
        'Created At',
    ];

    /**
     * The storage disk to use — matches FILESYSTEM_DISK env value.
     * On Vercel production this will be 's3' (Supabase).
     * Locally this will be 'local'.
     */
    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $diskName = config('filesystems.default', 'local');

        // Always use 'local' or 's3'; never 'public' for private exports
        if (! in_array($diskName, ['local', 's3'], true)) {
            $diskName = 'local';
        }

        return Storage::disk($diskName);
    }

    private function isS3(): bool
    {
        $diskName = config('filesystems.default', 'local');

        return $diskName === 's3';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Full rebuild of the Excel file from all sales in DB.
     */
    public function rebuildFromDatabase(): void
    {
        $sales = Sale::query()
            ->with(['user', 'customer'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $spreadsheet = $this->createFreshSpreadsheet($sales);
        $this->persistSpreadsheet($spreadsheet);
    }

    /**
     * Add or update a single sale row in the Excel file.
     */
    public function syncSale(Sale $sale): void
    {
        $sale->loadMissing(['user', 'customer']);

        $spreadsheet = $this->loadOrCreateSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $existingRow = $this->findSaleRow($sheet, $sale->id);

        if ($existingRow) {
            $this->writeSaleRow($sheet, $existingRow, $sale);
        } else {
            $lastRow = $this->getLastDataRow($sheet);
            $newRow = $lastRow + 1;
            $this->writeSaleRow($sheet, $newRow, $sale);
            $this->applyRowStyle($sheet, $newRow, $newRow % 2 === 0);
        }

        $this->persistSpreadsheet($spreadsheet);
    }

    /**
     * Remove a sale row from the Excel file.
     */
    public function removeSale(int $saleId): void
    {
        if (! $this->fileExists()) {
            return;
        }

        $spreadsheet = $this->loadOrCreateSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = $this->findSaleRow($sheet, $saleId);

        if ($row) {
            $sheet->removeRow($row);
            $this->persistSpreadsheet($spreadsheet);
        }
    }

    /**
     * Check whether the Excel file exists on the configured disk.
     */
    public function fileExists(): bool
    {
        return $this->disk()->exists(self::STORAGE_PATH);
    }

    /**
     * Get the absolute path (for local disk) or the S3 URL (for s3 disk).
     * Used only by the download controller — which streams content directly.
     */
    public function readContents(): string
    {
        return $this->disk()->get(self::STORAGE_PATH);
    }

    /**
     * Get last sync timestamp from disk metadata.
     */
    public function lastSyncedAt(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }

        try {
            $ts = $this->disk()->lastModified(self::STORAGE_PATH);

            return $ts ? date('d M Y, h:i A', $ts) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createFreshSpreadsheet(Collection $sales): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Records');

        $this->writeHeader($sheet);

        $rowIndex = 2;
        foreach ($sales as $sale) {
            $this->writeSaleRow($sheet, $rowIndex, $sale);
            $this->applyRowStyle($sheet, $rowIndex, $rowIndex % 2 === 0);
            $rowIndex++;
        }

        $this->autoSizeColumns($sheet);

        return $spreadsheet;
    }

    /**
     * Load existing spreadsheet from disk (local or S3), or build fresh.
     */
    private function loadOrCreateSpreadsheet(): Spreadsheet
    {
        if ($this->fileExists()) {
            try {
                if ($this->isS3()) {
                    // Download from S3 to a tmp file, then load
                    $contents = $this->disk()->get(self::STORAGE_PATH);
                    file_put_contents(self::TMP_PATH, $contents);
                    $loaded = IOFactory::load(self::TMP_PATH);
                    @unlink(self::TMP_PATH);

                    return $loaded;
                }

                // Local disk — load directly from path
                return IOFactory::load($this->localPath());
            } catch (\Throwable) {
                // Corrupt / unreadable — fall through to rebuild
            }
        }

        // Build fresh from DB
        $sales = Sale::query()
            ->with(['user', 'customer'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return $this->createFreshSpreadsheet($sales);
    }

    /**
     * Save the spreadsheet back to the configured disk (local or S3).
     */
    private function persistSpreadsheet(Spreadsheet $spreadsheet): void
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        if ($this->isS3()) {
            // Write to /tmp then upload to S3
            $writer->save(self::TMP_PATH);
            $this->disk()->put(
                self::STORAGE_PATH,
                file_get_contents(self::TMP_PATH),
                'private'
            );
            @unlink(self::TMP_PATH);
        } else {
            // Local disk — write directly
            $this->disk()->makeDirectory('exports');
            $writer->save($this->localPath());
        }
    }

    /**
     * Absolute local filesystem path (only valid when using local disk).
     */
    private function localPath(): string
    {
        return Storage::disk('local')->path(self::STORAGE_PATH);
    }

    private function writeHeader(Worksheet $sheet): void
    {
        $col = 'A';
        foreach (self::HEADERS as $header) {
            $sheet->setCellValue($col.'1', $header);
            $col++;
        }

        $lastCol = chr(ord('A') + count(self::HEADERS) - 1);
        $headerRange = 'A1:'.$lastCol.'1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1a56db'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
    }

    private function writeSaleRow(Worksheet $sheet, int $row, Sale $sale): void
    {
        $data = [
            $sale->id,
            $sale->date ? $sale->date->format('d/m/Y') : '',
            $sale->customer_name,
            $sale->customer?->phone ?? '',
            $sale->customer?->city ?? '',
            $sale->items_sold,
            $sale->quantity,
            (float) $sale->unit_price,
            (float) ($sale->discount ?? 0),
            (float) $sale->total_amount,
            ucfirst($sale->payment_status),
            $sale->notes ?? '',
            $sale->user?->name ?? '',
            $sale->created_at ? $sale->created_at->format('d/m/Y H:i') : '',
        ];

        $col = 'A';
        foreach ($data as $value) {
            $sheet->setCellValue($col.$row, $value);
            $col++;
        }

        // Payment status color
        $statusColor = match (strtolower($sale->payment_status)) {
            'paid' => '057a55',
            'partial' => 'c27803',
            default => 'c81e1e',
        };
        $sheet->getStyle('K'.$row)->applyFromArray([
            'font' => ['color' => ['rgb' => $statusColor], 'bold' => true],
        ]);

        // Number format for currency columns
        $sheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    private function applyRowStyle(Worksheet $sheet, int $row, bool $alternate): void
    {
        $lastCol = chr(ord('A') + count(self::HEADERS) - 1);
        $range = 'A'.$row.':'.$lastCol.$row;
        $bgColor = $alternate ? 'f0f4ff' : 'FFFFFF';

        $sheet->getStyle($range)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $bgColor],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'dde3f0'],
                ],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    private function autoSizeColumns(Worksheet $sheet): void
    {
        $lastCol = chr(ord('A') + count(self::HEADERS) - 1);
        for ($col = 'A'; $col <= $lastCol; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function findSaleRow(Worksheet $sheet, int $saleId): ?int
    {
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if ((int) $sheet->getCell('A'.$row)->getValue() === $saleId) {
                return $row;
            }
        }

        return null;
    }

    private function getLastDataRow(Worksheet $sheet): int
    {
        $highest = $sheet->getHighestRow();

        return $highest < 2 ? 1 : $highest;
    }
}
