<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $product->name }} - Details | Ashok Kumar Baans Store Karnal</title>
    <meta name="description" content="Buy {{ $product->name }} at wholesale rate ₹{{ number_format($product->unit_price, 2) }} from Ashok Kumar Baans Store in Karnal.">

    <link rel="icon" type="image/png" href="/favicon.png?v=1">
    <link rel="icon" href="/favicon.ico?v=1" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Header Navigation -->
    @include('partials.public-header', ['active' => 'catalog'])

    <!-- Breadcrumb Bar -->
    <div class="bg-slate-900 text-white border-b border-slate-800 py-3.5 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-emerald-400">Home</a>
                <span>/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-emerald-400">Catalog</a>
                @if ($product->category)
                <span>/</span>
                <a href="{{ route('catalog.index', ['category_id' => $product->category->id]) }}" class="hover:text-emerald-400">{{ $product->category->name }}</a>
                @endif
                <span>/</span>
                <span class="text-white font-bold truncate max-w-xs">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Details Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- Left Image -->
                <div class="lg:col-span-6 space-y-3">
                    <div class="relative rounded-2xl bg-slate-100 overflow-hidden border border-slate-200 aspect-4/3 flex items-center justify-center">
                        @if ($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover"
                            onerror="this.src='/images/bamboo_raw_poles.png'" />
                        @else
                        <div class="flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                            <span class="text-5xl mb-2">🎋</span>
                            <span class="text-xs font-bold text-slate-500">Ashok Kumar Baans Store</span>
                        </div>
                        @endif

                        @if ($product->category)
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] uppercase">
                            {{ $product->category->name }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Right Info -->
                <div class="lg:col-span-6 flex flex-col justify-between space-y-4">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                                {{ $product->category?->name ?? 'Category' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ')' : 'Out of Stock' }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                            {{ $product->name }}
                        </h1>

                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] font-bold text-emerald-800 uppercase">Wholesale Price</span>
                                <div class="text-2xl font-black text-emerald-700">
                                    ₹{{ number_format((float) $product->unit_price, 2) }}
                                    <span class="text-xs font-semibold text-slate-500">/ {{ $product->unit ?? 'pcs' }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-xs">
                                Direct Yard Rate
                            </span>
                        </div>

                        @if($product->description)
                        <div class="space-y-1">
                            <h3 class="text-xs font-bold text-slate-900 uppercase">Description</h3>
                            <p class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed">
                                {{ $product->description }}
                            </p>
                        </div>
                        @endif

                        <!-- Specs Table -->
                        <div class="space-y-1">
                            <h3 class="text-xs font-bold text-slate-900 uppercase">Product Details</h3>
                            <div class="rounded-xl border border-slate-200 overflow-hidden text-xs">
                                <div class="grid grid-cols-2 p-2.5 bg-slate-50 border-b border-slate-200">
                                    <span class="font-semibold text-slate-500">Type</span>
                                    <span class="font-bold text-slate-900">{{ $product->type === 'raw_material' ? 'Raw Material' : 'Finished Good' }}</span>
                                </div>
                                <div class="grid grid-cols-2 p-2.5 bg-white border-b border-slate-200">
                                    <span class="font-semibold text-slate-500">Unit</span>
                                    <span class="font-bold text-slate-900">{{ $product->unit ?? 'pcs' }}</span>
                                </div>
                                <div class="grid grid-cols-2 p-2.5 bg-slate-50">
                                    <span class="font-semibold text-slate-500">Location</span>
                                    <span class="font-bold text-slate-900">Janak Puri, Karnal, Haryana</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-slate-200 space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                            $waText = rawurlencode("Hello, I want to inquire about: " . $product->name . " (Price: ₹" . number_format((float)$product->unit_price, 2) . ")");
                            @endphp
                            <a href="https://wa.me/919254998000?text={{ $waText }}"
                                target="_blank"
                                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center justify-center gap-2">
                                <span>💬 WhatsApp Inquiry</span>
                            </a>

                            <a href="tel:+919254998000"
                                class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center gap-2">
                                <span>📞 Call (+91 92549 98000)</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if ($relatedProducts->count() > 0)
        <div class="mt-12 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-900">Related Products</h2>
                <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">View All →</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($relatedProducts as $relProduct)
                <div class="bg-white rounded-2xl border border-slate-200 p-3 space-y-2 hover:shadow-sm transition">
                    <div class="h-36 bg-slate-100 rounded-xl overflow-hidden relative">
                        @if ($relProduct->image_path)
                        <img src="{{ Storage::url($relProduct->image_path) }}" alt="{{ $relProduct->name }}" class="w-full h-full object-cover" />
                        @else
                        <div class="w-full h-full flex items-center justify-center text-3xl">🎋</div>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-900 text-xs truncate">
                        <a href="{{ route('catalog.show', $relProduct->id) }}">{{ $relProduct->name }}</a>
                    </h3>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-xs font-black text-emerald-700">₹{{ number_format((float) $relProduct->unit_price, 2) }}</span>
                        <a href="{{ route('catalog.show', $relProduct->id) }}" class="text-xs font-bold text-slate-700">Details →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Footer -->
    @include('partials.public-footer')
</body>

</html>