<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $product->name }} - Price & Details | Ashok Kumar Baans Store Karnal</title>
    <meta name="description" content="Buy {{ $product->name }} at wholesale rate ₹{{ number_format($product->unit_price, 2) }} per {{ $product->unit ?? 'pcs' }} from Ashok Kumar Baans Store in Karnal, Haryana.">

    <link rel="icon" type="image/png" href="/favicon.png?v=1">
    <link rel="icon" href="/favicon.ico?v=1" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Navigation Header -->
    @include('partials.public-header', ['active' => 'catalog'])

    <!-- Breadcrumb Bar -->
    <div class="bg-white border-b border-slate-200/80 py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 flex-wrap">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Home</a>
                <span>/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-emerald-600 transition">Products Catalog</a>
                @if ($product->category)
                    <span>/</span>
                    <a href="{{ route('catalog.index', ['category_id' => $product->category->id]) }}" class="hover:text-emerald-600 transition">{{ $product->category->name }}</a>
                @endif
                <span>/</span>
                <span class="text-slate-900 font-bold truncate max-w-xs">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Details Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <!-- Left Column: Product Image Gallery -->
                <div class="lg:col-span-6 space-y-4">
                    <div class="relative rounded-2xl bg-slate-100 overflow-hidden border border-slate-200 aspect-4/3 flex items-center justify-center">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover" 
                                 onerror="this.src='/images/bamboo_raw_poles.png'" />
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 p-8 text-center">
                                <span class="text-6xl mb-3">🎋</span>
                                <span class="text-sm font-bold text-slate-500">Ashok Kumar Baans Store Product</span>
                                <span class="text-xs text-slate-400 mt-1">Direct Yard Stock Karnal</span>
                            </div>
                        @endif

                        <!-- Category Tag & Type Badge Overlays -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2 items-start">
                            @if ($product->category)
                                <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-extrabold text-xs uppercase tracking-wider shadow-sm">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                            <span class="px-3 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-white font-bold text-xs">
                                {{ $product->type === 'raw_material' ? 'Raw Material' : 'Finished Good' }}
                            </span>
                        </div>
                    </div>

                    <!-- Trust Bar Below Image -->
                    <div class="grid grid-cols-3 gap-3 text-center pt-2">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="block text-xs font-black text-slate-900">Direct Merchant</span>
                            <span class="text-[10px] text-slate-500 font-medium">Yard Wholesale</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="block text-xs font-black text-slate-900">Truck Loading</span>
                            <span class="text-[10px] text-slate-500 font-medium">Karnal Haryana</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="block text-xs font-black text-slate-900">GST Invoice</span>
                            <span class="text-[10px] text-slate-500 font-medium">Transparent Bill</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Information & Action Options -->
                <div class="lg:col-span-6 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <!-- Category Badge & Stock Status -->
                        <div class="flex items-center justify-between gap-3">
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                                {{ $product->category?->name ?? 'General Category' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-black {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ' available)' : 'Out of Stock' }}
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h1 class="text-2xl sm:text-4xl font-black text-slate-900 leading-tight">
                            {{ $product->name }}
                        </h1>

                        <!-- Price Tag Box -->
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-extrabold text-emerald-800 uppercase tracking-wider">Wholesale Unit Price</span>
                                <div class="text-3xl font-black text-emerald-700 mt-0.5">
                                    ₹{{ number_format((float) $product->unit_price, 2) }}
                                    <span class="text-sm font-semibold text-slate-600">/ {{ $product->unit ?? 'pcs' }}</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-bold text-xs">
                                Direct Yard Rate
                            </span>
                        </div>

                        <!-- Product Description -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Description</h3>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700 leading-relaxed font-medium">
                                {{ $product->description ?: 'High tensile raw bamboo poles and custom construction scaffolding equipment supplied directly from Ashok Kumar Baans Store in Karnal, Haryana. Handcrafted to high durability standards.' }}
                            </div>
                        </div>

                        <!-- Specifications Table -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Product Specifications</h3>
                            <div class="rounded-2xl border border-slate-200 overflow-hidden text-xs">
                                <div class="grid grid-cols-2 p-3 bg-slate-50 border-b border-slate-200">
                                    <span class="font-bold text-slate-500">Item Type</span>
                                    <span class="font-extrabold text-slate-900">{{ $product->type === 'raw_material' ? 'Raw Material (Input Material)' : 'Finished Good (Sales Item)' }}</span>
                                </div>
                                <div class="grid grid-cols-2 p-3 bg-white border-b border-slate-200">
                                    <span class="font-bold text-slate-500">Category</span>
                                    <span class="font-extrabold text-slate-900">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                                <div class="grid grid-cols-2 p-3 bg-slate-50 border-b border-slate-200">
                                    <span class="font-bold text-slate-500">Unit of Measurement</span>
                                    <span class="font-extrabold text-slate-900">{{ $product->unit ?? 'pcs' }}</span>
                                </div>
                                <div class="grid grid-cols-2 p-3 bg-white border-b border-slate-200">
                                    <span class="font-bold text-slate-500">Stock Availability</span>
                                    <span class="font-extrabold text-emerald-700">{{ $product->stock_level }} {{ $product->unit ?? 'pcs' }} in Karnal Yard</span>
                                </div>
                                <div class="grid grid-cols-2 p-3 bg-slate-50">
                                    <span class="font-bold text-slate-500">Loading Location</span>
                                    <span class="font-extrabold text-slate-900">Janak Puri, Karnal, Haryana</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Call To Actions (WhatsApp + Phone + Form) -->
                    <div class="pt-4 border-t border-slate-200 space-y-3">
                        <span class="block text-xs font-extrabold text-slate-700">Inquire or Order this Product:</span>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- WhatsApp Inquiry Button -->
                            @php
                                $waText = rawurlencode("Hello Ashok Baans Store, I want to inquire about: " . $product->name . " (Price: ₹" . number_format((float)$product->unit_price, 2) . " / " . ($product->unit ?? 'pcs') . "). Please share availability.");
                            @endphp
                            <a href="https://wa.me/919254998000?text={{ $waText }}" 
                               target="_blank" 
                               class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                                <span class="text-base">💬</span>
                                <span>Inquire via WhatsApp</span>
                            </a>

                            <!-- Call Merchant Button -->
                            <a href="tel:+919254998000" 
                               class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                                <span class="text-base">📞</span>
                                <span>Call Shop (+91 92549 98000)</span>
                            </a>
                        </div>

                        <!-- Direct Inquiry Form Redirect Button -->
                        <a href="{{ route('contact') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition block text-center">
                            Submit Store Requirement Form →
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if ($relatedProducts->count() > 0)
            <div class="mt-16 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-600">RELATED PRODUCTS</span>
                        <h2 class="text-2xl font-black text-slate-900">More Products You Might Need</h2>
                    </div>
                    <a href="{{ route('catalog.index') }}" class="text-xs font-extrabold text-emerald-600 hover:underline">View All Catalog →</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($relatedProducts as $relProduct)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition duration-300 overflow-hidden flex flex-col justify-between group">
                            <div class="relative h-44 bg-slate-100 overflow-hidden">
                                @if ($relProduct->image_path)
                                    <img src="{{ Storage::url($relProduct->image_path) }}" 
                                         alt="{{ $relProduct->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                                         onerror="this.src='/images/bamboo_raw_poles.png'" />
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                        <span class="text-3xl">🎋</span>
                                    </div>
                                @endif

                                @if ($relProduct->category)
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-emerald-600 text-white font-black text-[9px] uppercase">
                                        {{ $relProduct->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-4 space-y-2">
                                <h3 class="font-extrabold text-slate-900 group-hover:text-emerald-600 text-xs truncate">
                                    <a href="{{ route('catalog.show', $relProduct->id) }}">{{ $relProduct->name }}</a>
                                </h3>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <span class="text-sm font-black text-emerald-700">₹{{ number_format((float) $relProduct->unit_price, 2) }}</span>
                                    <a href="{{ route('catalog.show', $relProduct->id) }}" class="text-xs font-bold text-slate-700 hover:text-emerald-600">Details →</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-600 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="font-semibold">© {{ date('Y') }} Ashok Kumar Baans Store, Karnal. All rights reserved.</span>
            <div class="flex items-center gap-6 font-bold text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Home</a>
                <a href="{{ route('catalog.index') }}" class="hover:text-emerald-600 transition">Products Catalog</a>
                <a href="{{ route('about') }}" class="hover:text-emerald-600 transition">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-emerald-600 transition">Contact Us</a>
            </div>
            <span class="text-emerald-600 font-extrabold">Ashok Kumar • Karnal, Haryana</span>
        </div>
    </footer>
</body>

</html>
