<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Stream sale invoice PDF inline in browser.
     */
    public function showSaleInvoice(Sale $sale)
    {
        Gate::authorize('view', $sale);

        return $this->invoiceService->streamSaleInvoice($sale);
    }

    /**
     * Download sale invoice PDF file.
     */
    public function downloadSaleInvoice(Sale $sale)
    {
        Gate::authorize('view', $sale);

        return $this->invoiceService->downloadSaleInvoice($sale);
    }

    /**
     * Printable HTML invoice view with instant print dialog.
     */
    public function printSaleInvoice(Sale $sale)
    {
        Gate::authorize('view', $sale);

        $sale->loadMissing(['customer', 'items.product', 'user']);

        $logoPath = public_path('dashboard-assets/images/ak-emblem.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;

        $storeDetails = Setting::getStoreDetails();

        $data = array_merge($storeDetails, [
            'sale' => $sale,
            'invoiceNumber' => ($storeDetails['invoicePrefix'] ?? 'INV-').str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'autoPrint' => true,
            'logoBase64' => $logoBase64,
        ]);

        return view('invoices.sale-invoice-print', $data);
    }

    /**
     * Public invoice view for customers opening invoice link from WhatsApp.
     * Protected by cryptographic URL signature to prevent IDOR / enumeration.
     */
    public function publicSaleInvoice(\Illuminate\Http\Request $request, Sale $sale)
    {
        if (! $request->hasValidSignature() && ! auth()->check()) {
            abort(403, __('Invalid or expired invoice link.'));
        }

        $sale->loadMissing(['customer', 'items.product', 'user']);

        $logoPath = public_path('dashboard-assets/images/ak-emblem.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;

        $storeDetails = Setting::getStoreDetails();

        $data = array_merge($storeDetails, [
            'sale' => $sale,
            'invoiceNumber' => ($storeDetails['invoicePrefix'] ?? 'INV-').str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'autoPrint' => false,
            'logoBase64' => $logoBase64,
        ]);

        return view('invoices.sale-invoice-print', $data);
    }

    /**
     * Public invoice PDF download for customers from WhatsApp.
     * Protected by cryptographic URL signature.
     */
    public function publicDownloadSaleInvoice(\Illuminate\Http\Request $request, Sale $sale)
    {
        if (! $request->hasValidSignature() && ! auth()->check()) {
            abort(403, __('Invalid or expired invoice download link.'));
        }

        return $this->invoiceService->downloadSaleInvoice($sale);
    }
}
