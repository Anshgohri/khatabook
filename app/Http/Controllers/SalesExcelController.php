<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ExcelSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesExcelController extends Controller
{
    public function __construct(private readonly ExcelSyncService $excelSync) {}

    /**
     * Download the live-synced customer sales Excel file.
     */
    public function download(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', Sale::class);

        if (! $this->excelSync->fileExists()) {
            // Build the file on first access if it doesn't exist yet
            $this->excelSync->rebuildFromDatabase();
        }

        $filename = 'customer_sales_'.now()->format('Y-m-d').'.xlsx';
        $contents = $this->excelSync->readContents();

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
