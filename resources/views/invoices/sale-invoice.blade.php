<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoiceNumber }} - {{ $storeName }}</title>
    <style>
        @page {
            margin: 25px 32px 40px 32px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            background-color: #ffffff;
            margin: 0;
            padding: 0 0 25px 0;
            line-height: 1.4;
        }
        
        footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 20px;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-logo-img {
            height: 56px;
            vertical-align: middle;
        }
        .brand-title-text {
            font-size: 21px;
            font-weight: bold;
            color: #0f172a;
            display: inline-block;
            vertical-align: middle;
            margin-left: 6px;
            letter-spacing: -0.5px;
        }
        .store-address-block {
            font-size: 9.5px;
            color: #334155;
            line-height: 1.45;
            margin-top: 6px;
        }

        .barcode-container {
            text-align: right;
        }
        .barcode-bars {
            display: inline-block;
            height: 32px;
        }
        .b-w1 { display: inline-block; width: 1px; height: 28px; background: #0f172a; margin-right: 1px; }
        .b-w2 { display: inline-block; width: 2px; height: 28px; background: #0f172a; margin-right: 1px; }
        .b-w3 { display: inline-block; width: 3px; height: 28px; background: #0f172a; margin-right: 1px; }
        .b-s1 { display: inline-block; width: 1px; height: 28px; background: transparent; margin-right: 1px; }
        .b-s2 { display: inline-block; width: 2px; height: 28px; background: transparent; margin-right: 1px; }
        .barcode-text {
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #0f172a;
            margin-top: 2px;
        }

        .divider-hr {
            border: none;
            border-top: 2px solid #0f172a;
            margin: 14px 0 16px 0;
        }

        .meta-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-grid-table td {
            vertical-align: top;
            padding-right: 10px;
        }
        .meta-label {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .meta-val {
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .items-table th {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 6px;
        }
        .items-table td {
            font-size: 10px;
            color: #1e293b;
            padding: 8px 6px;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .table-divider-bottom {
            border: none;
            border-top: 2px solid #0f172a;
            margin: 12px 0 18px 0;
        }

        .bottom-section {
            width: 100%;
            border-collapse: collapse;
        }
        .bottom-section td {
            vertical-align: top;
        }
        .payment-info-box {
            width: 56%;
            font-size: 9.5px;
            color: #334155;
            line-height: 1.45;
            padding-right: 20px;
        }
        .payment-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .totals-box {
            width: 44%;
            text-align: right;
        }
        .total-block {
            margin-bottom: 14px;
        }
        .total-label {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 65%; vertical-align: top;">
                <div>
                    @if (!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="brand-logo-img" alt="AK Logo">
                    @endif
                    <span class="brand-title-text">{{ $storeName }}</span>
                </div>
                <div class="store-address-block">
                    {{ $storeSubtitle }}<br>
                    {{ $storeAddress }}<br>
                    <strong>Phone:</strong> {{ $storePhone }}<br>
                    <strong>Email:</strong> {{ $storeEmail }}
                </div>
            </td>
            <td style="width: 35%; vertical-align: top;">
                <div class="barcode-container">
                    <div class="barcode-bars">
                        <span class="b-w2"></span><span class="b-s1"></span><span class="b-w1"></span><span class="b-s2"></span>
                        <span class="b-w3"></span><span class="b-s1"></span><span class="b-w1"></span><span class="b-s1"></span>
                        <span class="b-w2"></span><span class="b-s2"></span><span class="b-w1"></span><span class="b-s1"></span>
                        <span class="b-w3"></span><span class="b-s1"></span><span class="b-w2"></span><span class="b-s2"></span>
                        <span class="b-w1"></span><span class="b-s1"></span><span class="b-w3"></span><span class="b-s1"></span>
                        <span class="b-w2"></span><span class="b-s2"></span><span class="b-w1"></span><span class="b-s1"></span>
                        <span class="b-w3"></span><span class="b-s1"></span><span class="b-w2"></span><span class="b-s1"></span>
                        <span class="b-w1"></span><span class="b-s2"></span><span class="b-w2"></span><span class="b-s1"></span>
                    </div>
                    <div class="barcode-text">{{ $invoiceNumber }}</div>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider-hr">

    <!-- 4-Column Metadata Section -->
    <table class="meta-grid-table">
        <tr>
            <td style="width: 32%;">
                <div class="meta-label">Bill to</div>
                <div class="meta-val">
                    <strong>{{ $sale->customer_name }}</strong><br>
                    @if ($sale->customer?->phone || $sale->customer_phone)
                        Mobile: {{ $sale->customer?->phone ?: $sale->customer_phone }}<br>
                    @endif
                    @if ($sale->customer?->email && !str_contains($sale->customer->email, '@khatabook.customer'))
                        {{ $sale->customer->email }}<br>
                    @endif
                    @if ($sale->customer?->address || $sale->customer?->city)
                        {{ implode(', ', array_filter([$sale->customer?->address, $sale->customer?->city])) }}
                    @endif
                </div>
            </td>
            <td style="width: 22%;">
                <div style="margin-bottom: 12px;">
                    <div class="meta-label">Invoice number</div>
                    <div class="meta-val">{{ $invoiceNumber }}</div>
                </div>
                <div>
                    <div class="meta-label">Sale System ID</div>
                    <div class="meta-val">#{{ $sale->id }}</div>
                </div>
            </td>
            <td style="width: 22%;">
                <div style="margin-bottom: 12px;">
                    <div class="meta-label">Date</div>
                    <div class="meta-val">{{ $sale->date->format('m/d/Y') }}</div>
                </div>
                <div>
                    <div class="meta-label">Recorded By</div>
                    <div class="meta-val">{{ $sale->user?->name ?? 'Store Admin' }}</div>
                </div>
            </td>
            <td style="width: 24%;">
                <div style="margin-bottom: 12px;">
                    <div class="meta-label">Payment status</div>
                    <div class="meta-val" style="font-weight: bold; text-transform: uppercase; color: {{ $sale->payment_status === 'paid' ? '#047857' : ($sale->payment_status === 'partial' ? '#b45309' : '#b91c1c') }};">
                        {{ strtoupper($sale->payment_status) }}
                    </div>
                </div>
                <div>
                    <div class="meta-label">Payment terms</div>
                    <div class="meta-val">Due on Receipt</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 48%;" class="text-left">Item summary</th>
                <th style="width: 16%;" class="text-center">Qty</th>
                <th style="width: 18%;" class="text-right">Rate</th>
                <th style="width: 18%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sale->items as $item)
                <tr>
                    <td class="text-left">
                        <strong>{{ preg_replace('/\s*\(.*?\)/', '', $item->product?->name ?? $sale->items_sold) }}</strong>
                    </td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->product?->unit ?? 'pcs' }}</td>
                    <td class="text-right">&#8377;{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">&#8377;{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="text-left"><strong>{{ preg_replace('/\s*\(.*?\)/', '', $sale->items_sold) }}</strong></td>
                    <td class="text-center">{{ $sale->quantity }} pcs</td>
                    <td class="text-right">&#8377;{{ number_format((float) $sale->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">&#8377;{{ number_format((float) $sale->total_amount, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr class="table-divider-bottom">

    <!-- Bottom Summary & Bank / Terms Section -->
    <table class="bottom-section">
        <tr>
            <td class="payment-info-box">
                <div style="margin-bottom: 12px;">
                    <div class="payment-title">To pay by cash, UPI, or bank transfer:</div>
                    <strong>{{ $storeName }}</strong><br>
                    Phone: {{ $storePhone }}<br>
                    Email: {{ $storeEmail }}
                </div>

                @if ($sale->notes)
                    <div style="margin-bottom: 12px;">
                        <div class="payment-title">Memo / Remarks:</div>
                        {{ $sale->notes }}
                    </div>
                @endif

                <div>
                    <div class="payment-title">TERMS & CONDITIONS:</div>
                    {!! nl2br(e($storeTerms)) !!}
                </div>
            </td>
            <td class="totals-box">
                <div class="total-block">
                    <div class="total-label">Invoice total in INR</div>
                    <div class="total-amount">&#8377;{{ number_format((float) $sale->total_amount, 2) }}</div>
                </div>

                <div class="total-block">
                    <div class="total-label">Amount applied / Paid in INR</div>
                    <div class="total-amount" style="font-size: 16px; color: #475569;">
                        &#8377;{{ number_format($sale->payment_status === 'paid' ? (float)$sale->total_amount : 0, 2) }}
                    </div>
                </div>

                <div class="total-block">
                    <div class="total-label">Total amount due in INR</div>
                    <div class="total-amount" style="color: {{ $sale->payment_status === 'paid' ? '#047857' : '#0f172a' }};">
                        &#8377;{{ number_format($sale->payment_status === 'paid' ? 0 : (float)$sale->total_amount, 2) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Bar -->
    <footer>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left;">
                    <strong>Invoice number</strong> &nbsp;{{ $invoiceNumber }}
                </td>
                <td style="text-align: right;">
                    {{ $storeEmail }} &nbsp;|&nbsp; Page 1 of 1
                </td>
            </tr>
        </table>
    </footer>

</body>
</html>
