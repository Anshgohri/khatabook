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
    <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between no-print bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                ← Back to Sales List
            </a>
            <span class="font-black text-xs text-emerald-400">📄 Invoice {{ $invoiceNumber }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.sale.download', $sale->id) }}" class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-extrabold transition flex items-center gap-1.5">
                📥 Download PDF
            </a>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                🖨️ Print Invoice
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
                        @if (!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" class="h-14 object-contain" alt="AK Logo">
                        @endif
                        <span class="text-xl font-black text-slate-900 tracking-tight">{{ $storeName }}</span>
                    </div>
                    <div class="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                        {{ $storeSubtitle }}<br>
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
