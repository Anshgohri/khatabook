<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ashok Kumar Baans Store - Wholesale Bamboo, Ghodi, Chaali & Siddhi Merchant in Karnal</title>
    <meta name="description" content="Wholesale supplier of 15ft, 20ft & 25ft raw bamboo poles, scaffolding Ghodi trestles, woven Chaali platforms, and Siddhi ladders in Karnal, Haryana.">

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
    @include('partials.public-header', ['active' => 'home', 'categories' => $categories])

    <!-- Hero Section (Light E-Commerce Theme) -->
    <section class="relative bg-gradient-to-b from-emerald-50/70 via-white to-slate-50 border-b border-slate-200/60 py-12 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-black shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>DIRECT BAMBOO MERCHANT • KARNAL, HARYANA</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight">
                    Wholesale Raw Bamboo, <span class="text-emerald-600 underline decoration-amber-400 decoration-4 underline-offset-4">Ghodi, Chaali & Siddhi</span>
                </h1>

                <p class="text-slate-600 text-base sm:text-lg font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Direct timber yard supplier of 15ft, 20ft & 25ft raw bamboo poles, heavy-duty scaffolding Ghodi trestles, woven Chaali platforms, and sturdy Siddhi ladders. Stock loaded directly at our Karnal yard.
                </p>

                <!-- Search Input Bar & Quick Action -->
                <div class="pt-2 max-w-xl mx-auto lg:mx-0">
                    <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2.5 p-2 bg-white rounded-2xl shadow-lg border border-slate-200">
                        <div class="relative flex-1 w-full">
                            <span class="absolute left-3 top-3 text-slate-400">🔍</span>
                            <input type="text" 
                                   name="q" 
                                   placeholder="Search products (e.g. Baans 15 Feet, Ghodi)..." 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium border-0 focus:outline-none focus:ring-0 text-slate-900 placeholder-slate-400 bg-transparent" />
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black transition shadow-sm shrink-0">
                            Search Products
                        </button>
                    </form>
                </div>

                <!-- Trust Metrics Pills -->
                <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs font-bold text-slate-600">
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600 font-black">✓</span> Direct Wholesale Yard Rates
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600 font-black">✓</span> Heavy Contractor Loading
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600 font-black">✓</span> 15ft, 20ft, 25ft In Stock
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual Showcase -->
            <div class="lg:col-span-5 relative">
                <div class="relative rounded-3xl bg-white p-4 border border-slate-200 shadow-xl overflow-hidden group">
                    <div class="aspect-4/3 rounded-2xl overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/bamboo_hero_bg.png') }}" 
                             alt="Ashok Baans Store Yard" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                             onerror="this.src='/images/bamboo_raw_poles.png'" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <span class="px-2.5 py-1 rounded bg-amber-500 text-slate-950 font-black text-[10px] uppercase tracking-wider">KARNAL YARD STOCK</span>
                            <h3 class="text-lg font-black text-white mt-1">Ashok Kumar Baans Store</h3>
                            <p class="text-xs text-slate-200 font-medium">House No 2755, Janak Puri, Karnal, Haryana</p>
                        </div>
                    </div>

                    <!-- Quick Badge Overlay -->
                    <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="block text-lg font-black text-emerald-600">40+ Yrs</span>
                            <span class="text-[11px] font-bold text-slate-500">Market Trust</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="block text-lg font-black text-emerald-600">{{ $totalProductsCount }} Items</span>
                            <span class="text-[11px] font-bold text-slate-500">Admin Catalog</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin Categories Display Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-emerald-600">EXPLORE CATEGORIES</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Shop by Categories</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-extrabold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 group">
                <span>View All Categories</span>
                <span class="group-hover:translate-x-1 transition">→</span>
            </a>
        </div>

        <!-- Categories Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse ($categories as $cat)
                <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="group bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:shadow-md hover:border-emerald-500 transition duration-300 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-black group-hover:scale-110 transition duration-300">
                            @if(str_contains(strtolower($cat->name), 'raw'))
                                🎋
                            @elseif(str_contains(strtolower($cat->name), 'finished') || str_contains(strtolower($cat->name), 'ghodi'))
                                🪜
                            @elseif(str_contains(strtolower($cat->name), 'chaali'))
                                🧱
                            @else
                                📦
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">
                            {{ $cat->products_count }} {{ Str::plural('item', $cat->products_count) }}
                        </span>
                    </div>

                    <div>
                        <h3 class="font-extrabold text-slate-900 group-hover:text-emerald-600 text-base transition truncate">{{ $cat->name }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Browse admin added items</p>
                    </div>
                </a>
            @empty
                <!-- Fallback Default Categories if none added yet -->
                <div class="col-span-full bg-white p-6 rounded-2xl border border-dashed border-slate-300 text-center text-slate-500 text-sm">
                    No custom categories added by admin yet. Manage categories in the admin dashboard.
                </div>
            @endforelse
        </div>
    </section>

    <!-- Featured Admin Products Section (4 to 6 Products Showcase) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-slate-200/80">
        <div class="text-center max-w-3xl mx-auto mb-10 space-y-2">
            <span class="px-3.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-black uppercase tracking-wider">STORE INVENTORY</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900">Featured Products</h2>
            <p class="text-slate-600 text-sm font-semibold">
                High quality bamboo stock, scaffolding Ghodi trestles, Chaali mats, and Siddhi ladders added directly by store admin.
            </p>
        </div>

        <!-- 4 to 6 Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($featuredProducts as $product)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-lg transition duration-300 overflow-hidden flex flex-col justify-between group">
                    
                    <!-- Card Top Image -->
                    <div class="relative h-56 bg-slate-100 overflow-hidden">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                                 onerror="this.src='/images/bamboo_raw_poles.png'" />
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50 to-slate-100 text-slate-400">
                                <span class="text-5xl mb-2">🎋</span>
                                <span class="text-xs font-bold text-slate-400">Ashok Baans Store Product</span>
                            </div>
                        @endif

                        <!-- Badges Overlay -->
                        <div class="absolute top-3 left-3 flex flex-col gap-1.5 items-start">
                            @if ($product->category)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-extrabold text-[10px] shadow-sm uppercase tracking-wider">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-900/80 backdrop-blur-md text-white font-bold text-[10px]">
                                {{ $product->type === 'raw_material' ? 'Raw Material' : 'Finished Good' }}
                            </span>
                        </div>

                        <!-- Stock Badge -->
                        <div class="absolute bottom-3 right-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black shadow-sm {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ')' : 'Out of Stock' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-lg font-black text-slate-900 group-hover:text-emerald-600 transition leading-snug line-clamp-1">
                                <a href="{{ route('catalog.show', $product->id) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 font-medium line-clamp-2 leading-relaxed">
                                {{ $product->description ?: 'High quality bamboo stock supplied directly from Ashok Baans Store yard in Karnal.' }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase">Unit Rate</span>
                                <span class="text-xl font-black text-emerald-700">
                                    ₹{{ number_format((float) $product->unit_price, 2) }}
                                    <span class="text-xs font-semibold text-slate-500">/ {{ $product->unit ?? 'pcs' }}</span>
                                </span>
                            </div>

                            <a href="{{ route('catalog.show', $product->id) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-extrabold transition shadow-sm flex items-center gap-1">
                                <span>Details</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 bg-white rounded-2xl border border-dashed border-slate-300 text-center space-y-3">
                    <span class="text-4xl">📦</span>
                    <h4 class="text-lg font-bold text-slate-700">No Products Available Yet</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Products added by the admin will automatically appear here on the home page.</p>
                </div>
            @endforelse
        </div>

        <!-- Prominent Show More / View All Products Button -->
        <div class="mt-12 text-center">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm sm:text-base transition shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                <span>Show More Products (View All {{ $totalProductsCount }} Items)</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Why Choose Us & Store Advantages -->
    <section class="bg-white border-y border-slate-200/80 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-600">DIRECT MERCHANT GUARANTEE</span>
                <h2 class="text-3xl font-black text-slate-900">Why Buy from Ashok Baans Store?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-black">🎋</div>
                    <h3 class="text-base font-extrabold text-slate-900">Direct Wholesale Rates</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">Direct merchant yard pricing without middleman margins for contractors and builders.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-black">🚚</div>
                    <h3 class="text-base font-extrabold text-slate-900">Instant Truckload Loading</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">Ready loading yard in Janak Puri, Karnal for fast dispatch across Haryana.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-black">🪜</div>
                    <h3 class="text-base font-extrabold text-slate-900">Master Craftsmen</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">Handcrafted A-frame Ghodi trestles and tightly woven Chaali platforms built to standard.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl font-black">📋</div>
                    <h3 class="text-base font-extrabold text-slate-900">Digital Billing</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">Instant GST invoice & digital ledger management for customer peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Rate Inquiry Section -->
    <section id="contact" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-12">
            <span class="px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider">GET IN TOUCH</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900">Contact Us & Wholesale Rates</h2>
            <p class="text-slate-600 text-sm font-semibold">
                Have questions about bamboo rates, truckload orders, or custom Ghodi trestle sizes? Submit your inquiry directly to our Karnal office.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <!-- Shop Contact Info -->
            <div class="lg:col-span-5 flex flex-col">
                <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex-1 flex flex-col justify-between space-y-6">
                    <div class="space-y-6">
                        <div class="space-y-2 border-b border-slate-100 pb-5">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600">DIRECT STORE ADDRESS</span>
                            <h3 class="text-xl font-black text-slate-900">Ashok Kumar Baans Store</h3>
                            <p class="text-xs text-slate-600 font-medium leading-relaxed">
                                House No 2755, Opposite Gaushala Road,<br>
                                Janak Puri, Karnal, Haryana - 132001
                            </p>
                        </div>

                        <div class="space-y-3">
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600">PHONE CONTACTS</span>
                            <div class="space-y-2 text-slate-700 font-bold text-sm">
                                <div class="flex items-center gap-2">
                                    <span>📞</span>
                                    <span>Primary: <a href="tel:+919254998000" class="text-emerald-700 hover:underline">+91 92549 98000</a></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>📱</span>
                                    <span>Secondary: <a href="tel:+919255523276" class="text-emerald-700 hover:underline">+91 92555 23276</a></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-5 border-t border-slate-100">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">YARD WORKING HOURS</span>
                        <p class="text-xs text-slate-700 font-semibold">
                            Monday – Sunday: <span class="text-slate-900 font-extrabold">6:00 AM – 10:00 PM</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Inquiry Form -->
            <div class="lg:col-span-7 flex flex-col">
                <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex-1">
                    @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-extrabold text-center">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="name" class="block text-xs font-bold text-slate-700">Full Name *</label>
                                <input type="text" id="name" name="name" required placeholder="e.g. Rajesh Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div class="space-y-1.5">
                                <label for="phone" class="block text-xs font-bold text-slate-700">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required placeholder="e.g. 98123 45678" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="email" class="block text-xs font-bold text-slate-700">Email Address (Optional)</label>
                                <input type="email" id="email" name="email" placeholder="name@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div class="space-y-1.5">
                                <label for="inquiry_type" class="block text-xs font-bold text-slate-700">Inquiry Product *</label>
                                <select id="inquiry_type" name="inquiry_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="Scaffolding Ghodi">Scaffolding Ghodi (Trestles)</option>
                                    <option value="Bamboo Chaali">Woven Bamboo Chaali (Mats)</option>
                                    <option value="Bamboo Siddhi">Bamboo Siddhi (Ladders)</option>
                                    <option value="Raw Bamboo Poles">Raw Baans (15ft, 20ft, 25ft)</option>
                                    <option value="General Inquiry">General Wholesale Inquiry</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="message" class="block text-xs font-bold text-slate-700">Requirement Details *</label>
                            <textarea id="message" name="message" rows="3" required placeholder="Specify quantity, bamboo lengths, site location in Haryana..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center gap-2">
                            <span>Submit Inquiry to Store</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

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