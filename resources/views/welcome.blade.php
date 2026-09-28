<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ashok Kumar Baans Store - Wholesale Bamboo & Scaffolding Karnal</title>
    <meta name="description" content="Direct merchant supplier of 15ft, 20ft & 25ft raw bamboo poles, scaffolding Ghodi trestles, Siddi ladders, and Chaali platforms in Karnal, Haryana.">

    <link rel="icon" type="image/png" href="/favicon.png?v=1">
    <link rel="icon" href="/favicon.ico?v=1" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white" x-data="{ activeTab: 'all' }">

    <!-- Header Navigation -->
    @include('partials.public-header', ['active' => 'home', 'categories' => $categories])

    <!-- 1. Hero Showcase Banner (Spacious & Bold) -->
    <section class="bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white py-16 sm:py-24 lg:py-28 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.15),transparent_50%)]"></div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center relative z-10">

            <!-- Left Hero -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">

                <!-- Official Store Logo Header Pill -->
                <div class="inline-flex items-center gap-3.5 p-2 pr-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-xl">
                    <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-slate-950 p-1.5 flex items-center justify-center border border-emerald-500/60 shrink-0 shadow-lg">
                        <img src="{{ asset('images/ak-emblem.png') }}" alt="Ashok Kumar Baans Store Logo" class="h-full w-full object-contain" />
                    </div>
                    <div class="text-left">
                        <span class="text-[11px] font-medium tracking-widest text-emerald-400 uppercase block">ESTABLISHED TIMBER YARD</span>
                        <span class="text-xs sm:text-sm font-semibold text-white">ASHOK KUMAR BAANS STORE • KARNAL</span>
                    </div>
                </div>

                <h1 class="text-4xl sm:text-6xl lg:text-6xl font-semibold tracking-tight leading-none text-white">
                    Wholesale Raw Bamboo & <span class="text-emerald-400">Scaffolding Equipment</span>
                </h1>

                <p class="text-slate-300 text-base sm:text-xl font-normal max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Direct yard merchant supplier of 15ft, 20ft & 25ft raw bamboo poles, 4ft to 8ft scaffolding Ghodi trestles, Siddi ladders, and woven Chaali platforms.
                </p>

                <!-- Search Input Bar -->
                <div class="pt-4 max-w-xl mx-auto lg:mx-0">
                    <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 p-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl">
                        <input type="text"
                            name="q"
                            placeholder="Search raw bamboo, Ghodi, Siddi, Chaali..."
                            class="w-full px-5 py-3.5 rounded-xl text-sm sm:text-base font-medium text-white placeholder-slate-400 bg-transparent border-0 focus:outline-none" />
                        <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-sm sm:text-base shadow-lg transition shrink-0">
                            Search Products
                        </button>
                    </form>
                </div>

                <!-- Quick Yard Highlights -->
                <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs sm:text-sm font-medium text-slate-300">
                    <span class="flex items-center gap-2">
                        <span class="text-emerald-400 text-lg">✓</span> 100% Genuine Assam Timber
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="text-emerald-400 text-lg">✓</span> Truckload Yard Loading
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="text-emerald-400 text-lg">✓</span> Instant GST Invoice
                    </span>
                </div>
            </div>

            <!-- Right Hero Visual Card with Brand Logo Overlay -->
            <div class="lg:col-span-5">
                <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20 shadow-2xl space-y-6 relative">
                    <div class="aspect-4/3 sm:aspect-16/10 rounded-2xl overflow-hidden bg-slate-800 relative shadow-inner">
                        <img src="{{ asset('images/bamboo_hero_bg.png') }}"
                            alt="Ashok Baans Yard"
                            class="w-full h-full object-cover"
                            onerror="this.src='/images/bamboo_raw_poles.png'" />

                        <!-- Floating Emblem Badge -->
                        <div class="absolute top-3.5 left-3.5 z-10 p-2 pr-3.5 rounded-xl bg-slate-950/85 backdrop-blur-md border border-emerald-500/60 shadow-xl flex items-center gap-2.5">
                            <img src="{{ asset('images/ak-emblem.png') }}" alt="AK Emblem Logo" class="h-8 w-8 object-contain" />
                            <span class="text-[11px] font-medium tracking-wider text-emerald-400 uppercase">Verified Merchant</span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/30 to-transparent"></div>
                        <div class="absolute bottom-4 left-5 right-5 text-white flex items-end justify-between">
                            <div>
                                <span class="text-xs sm:text-sm font-medium text-emerald-400 uppercase tracking-wider block">Karnal Yard Inventory</span>
                                <h3 class="text-lg sm:text-xl font-semibold text-white">Ashok Kumar Baans Store</h3>
                                <p class="text-xs text-slate-300 font-normal">House No 2755, Janak Puri, Karnal</p>
                            </div>
                            <img src="{{ asset('images/ak-logo.png') }}" alt="Ashok Baans Logo" class="h-10 sm:h-12 object-contain opacity-90" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
                            <span class="block font-semibold text-emerald-400 text-xl sm:text-2xl">40+ Yrs</span>
                            <span class="text-xs text-slate-300 font-medium uppercase tracking-wider">Yard Legacy</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
                            <span class="block font-semibold text-emerald-400 text-xl sm:text-2xl">{{ $totalProductsCount }} Items</span>
                            <span class="text-xs text-slate-300 font-medium uppercase tracking-wider">In Stock Catalog</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Feature Highlights (Spacious 4 Grid) -->
    <section class="bg-white border-b border-slate-200 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-xs hover:shadow-md transition">
                <span class="text-4xl sm:text-5xl block mb-3">🎋</span>
                <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Genuine Timber</h3>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Hand-selected 15ft, 20ft & 25ft heavy bamboo poles</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-xs hover:shadow-md transition">
                <span class="text-4xl sm:text-5xl block mb-3">💰</span>
                <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Wholesale Rates</h3>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Direct merchant pricing with no middleman markup</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-xs hover:shadow-md transition">
                <span class="text-4xl sm:text-5xl block mb-3">🚚</span>
                <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Ready Dispatch</h3>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Same-day truckload & site delivery in Karnal region</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-xs hover:shadow-md transition">
                <span class="text-4xl sm:text-5xl block mb-3">📋</span>
                <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">GST Billing</h3>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Complete tax invoices & transparent Khatabook records</p>
            </div>
        </div>
    </section>

    <!-- 3. Shop by Category Grid -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs sm:text-sm font-medium text-emerald-600 uppercase tracking-widest block mb-1">YARD CATEGORIES</span>
                <h2 class="text-2xl sm:text-4xl font-semibold text-slate-900">Explore Products by Category</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm sm:text-base font-medium text-emerald-600 hover:text-emerald-700 hover:underline">
                <span>View Full Catalog</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($categories as $cat)
            <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="group bg-white rounded-2xl p-6 border-2 border-slate-200 hover:border-emerald-500 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div class="flex items-center gap-4 truncate">
                    <span class="text-3xl sm:text-4xl shrink-0">
                        @if(str_contains(strtolower($cat->name), 'raw'))
                        🎋
                        @elseif(str_contains(strtolower($cat->name), 'finished') || str_contains(strtolower($cat->name), 'ghodi'))
                        🪜
                        @elseif(str_contains(strtolower($cat->name), 'chaali'))
                        🧱
                        @else
                        📦
                        @endif
                    </span>
                    <div class="truncate">
                        <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600 text-base sm:text-lg truncate">{{ $cat->name }}</h3>
                        <span class="text-xs text-slate-500 font-normal">Browse Stock Items</span>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold shrink-0 ml-2 group-hover:bg-emerald-600 group-hover:text-white transition">
                    {{ $cat->products_count }}
                </span>
            </a>
            @empty
            <div class="col-span-full bg-white p-8 rounded-2xl border border-dashed border-slate-300 text-center text-slate-500 text-sm">
                No categories added yet.
            </div>
            @endforelse
        </div>
    </section>

    <!-- 4. Interactive Featured Database Products Showcase -->
    <section class="bg-slate-100/80 border-y border-slate-200 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
                <div>
                    <span class="text-xs sm:text-sm font-medium text-emerald-600 uppercase tracking-widest block mb-1">INVENTORY & STOCK</span>
                    <h2 class="text-2xl sm:text-4xl font-semibold text-slate-900">Featured Yard Products</h2>
                    <p class="text-sm text-slate-600 font-medium mt-1">Directly loaded from our live inventory database in Karnal</p>
                </div>

                <!-- Interactive Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 lg:pb-0">
                    <button @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer">
                        All Products ({{ $featuredProducts->count() }})
                    </button>
                    <button @click="activeTab = 'raw'"
                        :class="activeTab === 'raw' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer">
                        Raw Baans Poles
                    </button>
                    <button @click="activeTab = 'ghodi'"
                        :class="activeTab === 'ghodi' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer">
                        Scaffolding Ghodi
                    </button>
                    <button @click="activeTab = 'siddi'"
                        :class="activeTab === 'siddi' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer">
                        Baans Siddi (Ladders)
                    </button>
                </div>
            </div>

            <!-- DB Products Grid (Showing up to 12 items) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($featuredProducts as $product)
                @php
                $nameLower = strtolower($product->name);
                $itemType = 'all';
                if (str_contains($nameLower, 'baans') && !str_contains($nameLower, 'ghodi') && !str_contains($nameLower, 'siddi')) {
                $itemType = 'raw';
                } elseif (str_contains($nameLower, 'ghodi')) {
                $itemType = 'ghodi';
                } elseif (str_contains($nameLower, 'siddi') || str_contains($nameLower, 'ladder')) {
                $itemType = 'siddi';
                }
                @endphp

                <div x-show="activeTab === 'all' || activeTab === '{{ $itemType }}'"
                    x-transition
                    class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">

                    <!-- Image Container -->
                    <div class="relative h-56 sm:h-64 bg-slate-100 overflow-hidden">
                        @if ($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover group-hover:scale-108 transition duration-500"
                            onerror="this.src='/images/bamboo_raw_poles.png'" />
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-slate-400 text-5xl">
                            🎋
                        </div>
                        @endif

                        @if ($product->category)
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-lg bg-emerald-600 text-white font-medium text-xs uppercase tracking-wider shadow-md">
                            {{ $product->category->name }}
                        </span>
                        @else
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-lg bg-slate-900 text-white font-medium text-xs uppercase tracking-wider shadow-md">
                            Yard Stock
                        </span>
                        @endif

                        <span class="absolute bottom-3.5 right-3.5 px-3 py-1 rounded-full text-xs font-semibold shadow-sm {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-red-100 text-red-900 border border-red-300' }}">
                            {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ')' : 'Out of Stock' }}
                        </span>
                    </div>

                    <!-- Content Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-base sm:text-xl font-semibold text-slate-900 group-hover:text-emerald-600 transition truncate">
                                <a href="{{ route('catalog.show', $product->id) }}">{{ $product->name }}</a>
                            </h3>
                            @if($product->description)
                            <p class="text-xs sm:text-sm text-slate-600 font-medium line-clamp-2 mt-1 leading-relaxed">{{ $product->description }}</p>
                            @else
                            <p class="text-xs sm:text-sm text-slate-400 font-normal italic mt-1">High strength scaffolding & construction grade quality timber.</p>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div>
                                <span class="text-2xl sm:text-3xl font-semibold text-emerald-700">₹{{ number_format((float) $product->unit_price, 2) }}</span>
                                <span class="text-xs text-slate-500 font-medium block sm:inline">/ {{ $product->unit ?? 'pcs' }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('catalog.show', $product->id) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-900 font-semibold text-xs sm:text-sm transition">
                                    Details
                                </a>

                                @php
                                $waMsg = rawurlencode("Hello Ashok Baans Store Karnal, I want to inquire about: " . $product->name . " (Price: ₹" . number_format((float) $product->unit_price, 2) . ")");
                                @endphp
                                <a href="https://wa.me/919254998000?text={{ $waMsg }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm transition shadow-md flex items-center gap-1.5">
                                    <span>💬 Inquire</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-16 bg-white rounded-3xl border border-dashed border-slate-300 text-center text-sm text-slate-500">
                    No products available in database yet.
                </div>
                @endforelse
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-sm sm:text-base shadow-xl transition">
                    <span>View All Products in Full Catalog</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. NEW SECTION: Scaffolding & Timber Equipment Specs Spotlight -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs sm:text-sm font-medium text-emerald-600 uppercase tracking-widest block mb-1">SPECIFICATIONS & VARIETIES</span>
            <h2 class="text-2xl sm:text-4xl font-semibold text-slate-900">Scaffolding & Timber Yard Supplies</h2>
            <p class="text-sm sm:text-base text-slate-600 font-medium mt-2">
                We maintain ready yard stock for construction sites, plastering crews, shuttering contractors, and event setups.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Spec Card 1 -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-emerald-500 transition">
                <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-3xl font-semibold">
                    🎋
                </div>
                <h3 class="text-xl font-semibold text-slate-900">Raw Bamboo Poles (Baans)</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                    Heavy thick-wall bamboo poles sourced directly from eastern timber yards. Ideal for building scaffolding, shuttering support, and event structure.
                </p>
                <div class="pt-2 space-y-2 text-xs sm:text-sm font-medium text-slate-700">
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Standard Lengths: 15ft, 20ft & 25ft</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Heavy Load Bearing Capacity</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Weather Seasoned & Straight Poles</p>
                </div>
            </div>

            <!-- Spec Card 2 -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-emerald-500 transition">
                <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-3xl font-semibold">
                    🪜
                </div>
                <h3 class="text-xl font-semibold text-slate-900">Scaffolding Ghodi (Trestles)</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                    Heavy-duty iron pin welded bamboo trestles designed for plastering, painting, ceiling work, and masonry staging.
                </p>
                <div class="pt-2 space-y-2 text-xs sm:text-sm font-medium text-slate-700">
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Available Heights: 4ft, 5ft, 6ft, 7ft, 8ft</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Steel Pin Locking Mechanism</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> High Stability for Dual Workers</p>
                </div>
            </div>

            <!-- Spec Card 3 -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-emerald-500 transition">
                <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-3xl font-semibold">
                    🧱
                </div>
                <h3 class="text-xl font-semibold text-slate-900">Chaali Platforms & Siddi</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                    Tightly woven split bamboo Chaali mats and sturdy Siddi ladders for elevated working platforms and access.
                </p>
                <div class="pt-2 space-y-2 text-xs sm:text-sm font-medium text-slate-700">
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Woven Safety Walkway Mats</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Ladders: 10ft, 12ft, 15ft Heights</p>
                    <p class="flex items-center gap-2"><span class="text-emerald-600">•</span> Double Reinforced Rung Joints</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. NEW SECTION: Wholesale Bulk Dispatch Callout Banner -->
    <section class="bg-slate-900 text-white py-16 px-4 sm:px-6 lg:px-8 border-y border-slate-800 relative overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-8 space-y-4 text-center lg:text-left">
                <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs sm:text-sm font-semibold uppercase tracking-widest">
                    BULK ORDER DISCOUNTS
                </span>
                <h2 class="text-2xl sm:text-4xl font-semibold text-white">
                    Need 500+ Bamboo Poles or Bulk Ghodi Sets?
                </h2>
                <p class="text-slate-300 text-sm sm:text-lg max-w-2xl font-medium">
                    We supply full truckloads directly from our Karnal yard for construction projects across Karnal, Panipat, Kurukshetra, Kaithal, and Delhi-NCR.
                </p>
            </div>
            <div class="lg:col-span-4 text-center lg:text-right">
                <a href="https://wa.me/919254998000?text=Hello%20Ashok%20Baans%20Store%2C%20I%20want%20to%20get%20a%20wholesale%20bulk%20quote%20for%20a%20construction%20project."
                    target="_blank"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-sm sm:text-base shadow-2xl transition">
                    <span>💬 Get Wholesale Quote on WhatsApp</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 7. Quick Store Inquiry Section (Scaled up) -->
    <section id="contact" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            <!-- Contact Info Card -->
            <div class="lg:col-span-5 bg-white p-8 rounded-3xl border border-slate-200 shadow-md space-y-6 text-sm">
                <div>
                    <span class="font-semibold uppercase text-xs text-emerald-600 tracking-widest block mb-1">VISIT OUR TIMBER YARD</span>
                    <h3 class="text-2xl font-semibold text-slate-900">Ashok Kumar Baans Store</h3>
                </div>

                <p class="text-slate-600 font-medium leading-relaxed">
                    House No 2755, Opposite Gaushala Road,<br>
                    Janak Puri, Karnal, Haryana - 132001
                </p>

                <div class="pt-4 border-t border-slate-100 space-y-3 font-medium text-slate-800">
                    <p class="flex items-center gap-3">
                        <span class="text-xl">📞</span>
                        <span>Primary Phone:</span>
                        <a href="tel:+919254998000" class="text-emerald-600 hover:underline font-semibold">+91 92549 98000</a>
                    </p>
                    <p class="flex items-center gap-3">
                        <span class="text-xl">📱</span>
                        <span>Secondary Phone:</span>
                        <a href="tel:+919255523276" class="text-emerald-600 hover:underline font-semibold">+91 92555 23276</a>
                    </p>
                    <p class="flex items-center gap-3 text-slate-600 font-medium">
                        <span class="text-xl">🕒</span>
                        <span>Yard Timings: 8:00 AM - 8:00 PM (Mon-Sat)</span>
                    </p>
                </div>

                <div class="pt-4">
                    <a href="https://wa.me/919254998000" target="_blank" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm flex items-center justify-center gap-2 transition shadow-md">
                        <span>💬 Chat on WhatsApp (+91 92549 98000)</span>
                    </a>
                </div>
            </div>

            <!-- Inquiry Form Card -->
            <div class="lg:col-span-7 bg-white p-8 rounded-3xl border border-slate-200 shadow-md">
                <div class="mb-6">
                    <span class="font-semibold uppercase text-xs text-emerald-600 tracking-widest block mb-1">DIRECT INQUIRY</span>
                    <h3 class="text-2xl font-semibold text-slate-900">Send Requirement Details</h3>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium">Submit your requirement for immediate callback and price quotation.</p>
                </div>

                @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium text-center">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-5 text-sm">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block font-semibold text-slate-800 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Enter your full name" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                        </div>

                        <div>
                            <label for="phone" class="block font-semibold text-slate-800 mb-2">Phone / WhatsApp *</label>
                            <input type="tel" id="phone" name="phone" required placeholder="Enter 10-digit mobile number" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="inquiry_type" class="block font-semibold text-slate-800 mb-2">Product Category *</label>
                        <select id="inquiry_type" name="inquiry_type" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="Raw Bamboo Poles">Raw Bamboo Poles (15ft, 20ft, 25ft)</option>
                            <option value="Scaffolding Ghodi">Scaffolding Ghodi (Trestles - 4ft to 8ft)</option>
                            <option value="Bamboo Siddhi">Bamboo Siddhi (Ladders)</option>
                            <option value="Bamboo Chaali">Woven Bamboo Chaali (Platform Mats)</option>
                            <option value="General Inquiry">General Wholesale / Contractor Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block font-semibold text-slate-800 mb-2">Requirement Details *</label>
                        <textarea id="message" name="message" rows="3" required placeholder="Specify quantity needed, length requirements, delivery site location..." class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm sm:text-base font-semibold transition shadow-lg">
                        Submit Requirement Inquiry
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    @include('partials.public-footer', ['categories' => $categories])
</body>

</html>