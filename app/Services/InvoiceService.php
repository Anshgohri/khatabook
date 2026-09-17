<?php

namespace App\Services;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceService
{
    /**
     * Generate PDF object for a Sale invoice.
     */
    public function makeSaleInvoicePdf(Sale $sale)
    {
        $sale->loadMissing(['customer', 'items.product', 'user']);

        $logoPath = public_path('images/ak-emblem.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        $storeDetails = \App\Models\Setting::getStoreDetails();

        $data = array_merge($storeDetails, [
            'sale' => $sale,
            'invoiceNumber' => ($storeDetails['invoicePrefix'] ?? 'INV-') . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'logoBase64' => $logoBase64,
        ]);

        return Pdf::loadView('invoices.sale-invoice', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Stream PDF invoice inline to browser for viewing/printing.
     */
    public function streamSaleInvoice(Sale $sale): Response
    {
        $pdf = $this->makeSaleInvoicePdf($sale);
        $filename = 'Invoice-INV-' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download PDF invoice as file.
     */
    public function downloadSaleInvoice(Sale $sale): Response
    {
        $pdf = $this->makeSaleInvoicePdf($sale);
        $filename = 'Invoice-INV-' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Reusable generic function to render invoice PDF from any custom data/template.
     */
    public function makeGenericInvoicePdf(string $view, array $data, string $paper = 'a4', string $orientation = 'portrait')
    {
        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }
}
