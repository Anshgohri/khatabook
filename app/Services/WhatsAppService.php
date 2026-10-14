<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Setting;

class WhatsAppService
{
    /**
     * Check if WhatsApp service is enabled in store settings.
     */
    public static function isEnabled(): bool
    {
        return (bool) Setting::get('whatsapp_enabled', '1');
    }

    /**
     * Sanitize and format phone number for WhatsApp Click-to-Chat URL.
     * Defaults to India country code (91) for 10-digit mobile numbers.
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Keep only digits
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Standard 10-digit Indian mobile number
        if (strlen($cleaned) === 10) {
            return '91' . $cleaned;
        }

        // 11 digits starting with 0 (e.g. 09876543210)
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '0')) {
            return '91' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Generate a polite, pleasant, and complete WhatsApp invoice message.
     */
    public static function buildSaleInvoiceMessage(Sale $sale): string
    {
        $sale->loadMissing(['customer', 'items.product', 'user']);
        $storeDetails = Setting::getStoreDetails();
        $storeName = $storeDetails['storeName'] ?? config('khatabook.store_name', 'Our Store');
        $invoicePrefix = $storeDetails['invoicePrefix'] ?? 'INV-';
        $invoiceNumber = $invoicePrefix . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT);
        $date = $sale->date ? $sale->date->format('d M Y') : now()->format('d M Y');
        $customerName = $sale->customer_name ?: ($sale->customer?->name ?? 'Valued Customer');

        $message = "🙏 *Thanks for purchasing goods and services from {$storeName}!* ✨\n\n";
        $message .= "Here are your invoice details:\n";
        $message .= "📄 *Invoice No:* #{$invoiceNumber}\n";
        $message .= "📅 *Date:* {$date}\n";
        $message .= "👤 *Customer:* {$customerName}\n\n";

        $message .= "📦 *Purchased Items:*\n";
        if ($sale->items->isNotEmpty()) {
            foreach ($sale->items as $item) {
                $pName = $item->product?->name ?? 'Item';
                $lineTotal = number_format((float) ($item->total_price ?? ($item->quantity * $item->unit_price)), 2);
                $unitPrice = number_format((float) $item->unit_price, 2);
                $message .= "• {$pName} (x{$item->quantity} @ ₹{$unitPrice}) = ₹{$lineTotal}\n";
            }
        } else {
            $message .= "• {$sale->items_sold} (x{$sale->quantity}) = ₹" . number_format((float) $sale->total_amount, 2) . "\n";
        }

        $subtotal = 0;
        foreach ($sale->items as $item) {
            $subtotal += (float) ($item->total_price ?? ($item->quantity * $item->unit_price));
        }
        if ($subtotal == 0) {
            $subtotal = (float) $sale->total_amount + (float) ($sale->discount ?? 0);
        }

        if ((float) $sale->discount > 0) {
            $message .= "\n💵 *Subtotal:* ₹" . number_format($subtotal, 2) . "\n";
            $message .= "🏷️ *Discount:* ₹" . number_format((float) $sale->discount, 2) . "\n";
        }

        $message .= "\n💰 *Total Amount:* ₹" . number_format((float) $sale->total_amount, 2) . "\n";
        $message .= "💳 *Payment Status:* " . ucfirst($sale->payment_status) . "\n\n";

        // Cryptographically signed invoice link preventing IDOR / enumeration attacks
        $invoiceLink = \Illuminate\Support\Facades\URL::signedRoute('invoices.sale.public', ['sale' => $sale->id]);
        $message .= "🔗 *View / Print Tax Invoice:*\n{$invoiceLink}\n\n";

        if (!empty($storeDetails['storeAddress'])) {
            $message .= "📍 *Store Address:* {$storeDetails['storeAddress']}\n";
        }
        if (!empty($storeDetails['storePhone'])) {
            $message .= "📞 *Contact:* {$storeDetails['storePhone']}\n";
        }

        $message .= "\n🤝 *We appreciate your business and look forward to serving you again!*";

        return $message;
    }

    /**
     * Build the direct WhatsApp Click-to-Chat URL.
     */
    public static function getSaleWhatsAppUrl(Sale $sale, ?string $overridePhone = null): string
    {
        $rawPhone = $overridePhone ?: ($sale->customer?->phone ?: null);
        $phone = static::formatPhoneNumber($rawPhone);
        $message = static::buildSaleInvoiceMessage($sale);

        if (!empty($phone)) {
            return "https://api.whatsapp.com/send?phone={$phone}&text=" . rawurlencode($message);
        }

        return "https://api.whatsapp.com/send?text=" . rawurlencode($message);
    }
}
