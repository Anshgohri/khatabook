<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>All Products Catalog - Ashok Kumar Baans Store, Karnal</title>
    <meta name="description" content="Browse all raw bamboo poles, scaffolding Ghodi trestles, Chaali platforms, and Siddhi ladders added by admin at Ashok Kumar Baans Store in Karnal.">

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

    <!-- Header -->
    @include('partials.public-header', ['active' => 'catalog', 'categories' => $categories])

    <!-- Page Title & Breadcrumbs Banner -->
    <div class="bg-white border-b border-slate-200/80 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2">
                    <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Home</a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold">Products Catalog</span>
                    @if ($selectedCategory)
                        <span>/</span>
                        <span class="text-emerald-600 font-bold">{{ $selectedCategory->name }}</span>
                    @endif
                </nav>

                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    @if ($selectedCategory)
                        Category: <span class="text-emerald-600">{{ $selectedCategory->name }}</span>
                    @elseif (request('q'))
                        Search results for: <span class="text-emerald-600">"{{ request('q') }}"</span>
                    @else
                        All Products Catalog
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                    Showing {{ $products->total() }} total {{ Str::plural('product', $products->total()) }} added by admin.
                </p>
            </div>

            <!-- Clear Search / Filters if active -->
            @if (request('category_id') || request('q') || request('type'))
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition self-start md:self-center">
                    <span>✕ Clear Filters</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Main Catalog Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Filter Controls Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs mb-8 space-y-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                
                <!-- Category Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-none">
                    <a href="{{ route('catalog.index', array_filter(['q' => request('q'), 'type' => request('type')])) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap transition {{ !request('category_id') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        All Categories
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('catalog.index', array_filter(['category_id' => $cat->id, 'q' => request('q'), 'type' => request('type')])) }}" 
                           class="px-4 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 {{ request('category_id') == $cat->id ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            <span>{{ $cat->name }}</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ request('category_id') == $cat->id ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $cat->products_count }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Type Filter Dropdown -->
                <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto shrink-0">
                    @if (request('category_id'))
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}" />
                    @endif
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}" />
                    @endif

                    <select name="type" onchange="this.form.submit()" class="w-full md:w-auto px-3.5 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Item Types</option>
                        <option value="finished_good" {{ request('type') === 'finished_good' ? 'selected' : '' }}>Finished Goods (Sales)</option>
                        <option value="raw_material" {{ request('type') === 'raw_material' ? 'selected' : '' }}>Raw Materials</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($products as $product)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg transition duration-300 overflow-hidden flex flex-col justify-between group">
                    
                    <!-- Product Top Image -->
                    <div class="relative h-48 bg-slate-100 overflow-hidden">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                                 onerror="this.src='/images/bamboo_raw_poles.png'" />
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50 to-slate-100 text-slate-400">
                                <span class="text-4xl mb-1">🎋</span>
                                <span class="text-[10px] font-bold text-slate-400">Ashok Baans Store</span>
                            </div>
                        @endif

                        <!-- Category Tag Overlay -->
                        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start">
                            @if ($product->category)
                                <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-black text-[9px] uppercase tracking-wider">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Stock Badge Overlay -->
                        <div class="absolute bottom-2.5 right-2.5">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black shadow-sm {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ')' : 'Out of Stock' }}
                            </span>
                        </div>
                    </div>

                    <!-- Product Details Body -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-1">
                            <h3 class="font-extrabold text-slate-900 group-hover:text-emerald-600 transition text-sm leading-snug line-clamp-1">
                                <a href="{{ route('catalog.show', $product->id) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 font-medium line-clamp-2 leading-relaxed">
                                {{ $product->description ?: 'Raw bamboo stock and scaffolding equipment supplied directly from Karnal store yard.' }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="block text-[9px] font-bold text-slate-400 uppercase">Unit Price</span>
                                <span class="text-base font-black text-emerald-700">
                                    ₹{{ number_format((float) $product->unit_price, 2) }}
                                    <span class="text-[10px] font-semibold text-slate-500">/ {{ $product->unit ?? 'pcs' }}</span>
                                </span>
                            </div>

                            <a href="{{ route('catalog.show', $product->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-emerald-600 text-white text-xs font-extrabold transition shadow-xs flex items-center gap-1">
                                <span>View</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 bg-white rounded-2xl border border-dashed border-slate-300 text-center space-y-3">
                    <span class="text-4xl">🔍</span>
                    <h3 class="text-lg font-bold text-slate-700">No Products Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Try adjusting your search query or selecting a different category filter.</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold shadow-sm">
                        View All Products
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $products->links() }}
        </div>
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
