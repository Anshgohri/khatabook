<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice {{ $invoiceNumber }} - {{ $storeName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900 p-4 sm:p-6 font-sans">

    <!-- Top Floating Print Action Bar -->
    <div class="max-w-4xl mx-auto mb-4 flex flex-wrap items-center justify-between gap-2 no-print bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            @auth
            <a href="{{ route('sales') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                ← Back to Sales List
            </a>
            @else
            <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                🏠 {{ $storeName }}
            </a>
            @endauth
            <span class="font-black text-xs text-emerald-400">📄 Invoice {{ $invoiceNumber }}</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if (\App\Services\WhatsAppService::isEnabled())
            <a href="{{ \App\Services\WhatsAppService::getSaleWhatsAppUrl($sale) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.42 0-2.82-.37-4.06-1.07l-.29-.17-3.12.82.83-3.04-.19-.3a8.17 8.17 0 0 1-1.26-4.48c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43s-.56-1.35-.77-1.85c-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.07s.89 2.4 1.02 2.57c.12.17 1.76 2.68 4.25 3.76.59.26 1.06.41 1.42.53.6.19 1.14.16 1.57.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                </svg>
                WhatsApp Invoice
            </a>
            @endif
            <a href="{{ auth()->check() ? route('invoices.sale.download', $sale->id) : \Illuminate\Support\Facades\URL::signedRoute('invoices.sale.public.download', ['sale' => $sale->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-extrabold transition flex items-center gap-1.5">
                📥 PDF
            </a>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-white text-slate-900 text-xs font-black transition flex items-center gap-1.5 shadow-md">
                🖨️ Print
            </button>
        </div>
    </div>

    <!-- Main Printable Paper Container -->
    <div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-200 page-container flex flex-col justify-between">
        
        <div>
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <x-app-logo textColor="#0f172a" height="56px" />
                    </div>
                    <div class="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                        Prop. {{ $storeProprietor }} &bull; {{ $storeSubtitle }}<br>
                        {{ $storeAddress }}<br>
                        <strong>Phone:</strong> {{ $storePhone }}<br>
                        <strong>Email:</strong> {{ $storeEmail }}
                    </div>
                </div>

                <!-- Barcode -->
                <div class="text-right">
                    <div class="inline-flex h-8 items-center gap-0.5">
                        <span class="w-0.5 h-7 bg-slate-900"></span><span class="w-1 h-7 bg-slate-900"></span><span class="w-0.5 h-7 bg-slate-900"></span>
                        <span class="w-1.5 h-7 bg-slate-900"></span><span class="w-0.5 h-7 bg-slate-900"></span><span class="w-1 h-7 bg-slate-900"></span>
                        <span class="w-0.5 h-7 bg-slate-900"></span><span class="w-1.5 h-7 bg-slate-900"></span><span class="w-0.5 h-7 bg-slate-900"></span>
                        <span class="w-1 h-7 bg-slate-900"></span><span class="w-0.5 h-7 bg-slate-900"></span><span class="w-1.5 h-7 bg-slate-900"></span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-900 tracking-widest mt-0.5">{{ $invoiceNumber }}</p>
                </div>
            </div>

            <hr class="border-t-2 border-slate-900 my-4">

            <!-- 4-Column Metadata -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 text-[11px]">
                <div>
                    <p class="font-bold text-slate-900 mb-0.5">Bill to</p>
                    <p class="font-bold text-slate-900 text-xs">{{ $sale->customer_name }}</p>
                    @if ($sale->customer?->phone || $sale->customer_phone)
                        <p class="text-slate-600">Mobile: {{ $sale->customer?->phone ?: $sale->customer_phone }}</p>
                    @endif
                    @if ($sale->customer?->email && !str_contains($sale->customer->email, '@khatabook.customer'))
                        <p class="text-slate-600">{{ $sale->customer->email }}</p>
                    @endif
                    @if ($sale->customer?->address || $sale->customer?->city)
                        <p class="text-slate-600">{{ implode(', ', array_filter([$sale->customer?->address, $sale->customer?->city])) }}</p>
                    @endif
                </div>

                <div>
                    <div class="mb-2">
                        <p class="font-bold text-slate-900">Invoice number</p>
                        <p class="text-slate-700">{{ $invoiceNumber }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">Sale System ID</p>
                        <p class="text-slate-700">#{{ $sale->id }}</p>
                    </div>
                </div>

                <div>
                    <div class="mb-2">
                        <p class="font-bold text-slate-900">Date</p>
                        <p class="text-slate-700">{{ $sale->date->format('m/d/Y') }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">Recorded By</p>
                        <p class="text-slate-700">{{ $sale->user?->name ?? 'Store Admin' }}</p>
                    </div>
                </div>

                <div>
                    <div class="mb-2">
                        <p class="font-bold text-slate-900">Payment status</p>
                        <p class="font-extrabold uppercase {{ $sale->payment_status === 'paid' ? 'text-emerald-700' : ($sale->payment_status === 'partial' ? 'text-amber-700' : 'text-red-700') }}">
                            {{ strtoupper($sale->payment_status) }}
                        </p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">Payment terms</p>
                        <p class="text-slate-700">Due on Receipt</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-4">
                <table class="w-full text-left border-collapse text-[11px]">
                    <thead>
                        <tr class="border-y border-slate-300 font-bold text-slate-900">
                            <th class="py-2 text-left w-1/2">Item summary</th>
                            <th class="py-2 text-center w-20">Qty</th>
                            <th class="py-2 text-right w-28">Rate</th>
                            <th class="py-2 text-right w-28">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($sale->items as $item)
                            <tr>
                                <td class="py-2.5 text-left font-bold text-slate-900">
                                    {{ preg_replace('/\s*\(.*?\)/', '', $item->product?->name ?? $sale->items_sold) }}
                                </td>
                                <td class="py-2.5 text-center font-medium text-slate-700">{{ $item->quantity }} {{ $item->product?->unit ?? 'pcs' }}</td>
                                <td class="py-2.5 text-right text-slate-700">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-2.5 text-right font-bold text-slate-900">₹{{ number_format((float) $item->total_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-2.5 text-left font-bold text-slate-900">{{ preg_replace('/\s*\(.*?\)/', '', $sale->items_sold) }}</td>
                                <td class="py-2.5 text-center font-medium text-slate-700">{{ $sale->quantity }} pcs</td>
                                <td class="py-2.5 text-right text-slate-700">₹{{ number_format((float) $sale->unit_price, 2) }}</td>
                                <td class="py-2.5 text-right font-bold text-slate-900">₹{{ number_format((float) $sale->total_amount, 2) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr class="border-t-2 border-slate-900 mb-6">

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 text-[11px]">
                <div class="sm:col-span-7 space-y-3 text-slate-600">
                    <div>
                        <p class="font-bold text-slate-900 mb-0.5">To pay by cash, UPI, or bank transfer:</p>
                        <p class="font-bold text-slate-900">{{ $storeName }}</p>
                        <p>Phone: {{ $storePhone }}</p>
                        <p>Email: {{ $storeEmail }}</p>
                    </div>

                    @if ($sale->notes)
                        <div>
                            <p class="font-bold text-slate-900 mb-0.5">Memo / Remarks:</p>
                            <p class="text-slate-700">{{ $sale->notes }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="font-bold text-slate-900 mb-0.5">TERMS & CONDITIONS:</p>
                        <div class="leading-relaxed">
                            {!! nl2br(e($storeTerms)) !!}
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-5 text-right space-y-3">
                    <div>
                        <p class="font-bold text-slate-900 text-[11px]">Invoice total in INR</p>
                        <p class="text-2xl font-black text-slate-900 mt-0.5">₹{{ number_format((float) $sale->total_amount, 2) }}</p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 text-[11px]">Amount applied / Paid in INR</p>
                        <p class="text-base font-extrabold text-slate-600 mt-0.5">
                            ₹{{ number_format($sale->payment_status === 'paid' ? (float)$sale->total_amount : 0, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 text-[11px]">Total amount due in INR</p>
                        <p class="text-2xl font-black {{ $sale->payment_status === 'paid' ? 'text-emerald-700' : 'text-slate-900' }} mt-0.5">
                            ₹{{ number_format($sale->payment_status === 'paid' ? 0 : (float)$sale->total_amount, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bar -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200 text-[10px] text-slate-500 mt-8">
            <div>
                <strong>Invoice number</strong> &nbsp;{{ $invoiceNumber }}
            </div>
            <div>
                {{ $storeEmail }} &nbsp;|&nbsp; Page 1 of 1
            </div>
        </div>

    </div>

    @if (request()->has('autoPrint') || !empty($autoPrint))
        <script>
            window.addEventListener('load', () => {
                setTimeout(() => { window.print(); }, 400);
            });
        </script>
    @endif
</body>
</html>
