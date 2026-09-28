<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Products Catalog - Ashok Kumar Baans Store, Karnal</title>
    <meta name="description" content="Browse all raw bamboo poles, scaffolding Ghodi trestles, and Chaali platforms added by admin at Ashok Kumar Baans Store in Karnal.">

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
    @include('partials.public-header', ['active' => 'catalog', 'categories' => $categories])

    <!-- Page Title & Breadcrumb Banner -->
    <div class="bg-slate-900 text-white border-b border-slate-800 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                    <a href="{{ route('home') }}" class="hover:text-emerald-400">Home</a>
                    <span>/</span>
                    <span class="text-white font-bold">Catalog</span>
                    @if ($selectedCategory)
                    <span>/</span>
                    <span class="text-emerald-400 font-bold">{{ $selectedCategory->name }}</span>
                    @endif
                </nav>

                <h1 class="text-2xl sm:text-3xl font-semibold text-white">
                    @if ($selectedCategory)
                    {{ $selectedCategory->name }}
                    @elseif (request('q'))
                    Search: "{{ request('q') }}"
                    @else
                    Products Catalog
                    @endif
                </h1>
                <p class="text-xs text-slate-300 font-medium mt-0.5">
                    Showing {{ $products->total() }} total items from database.
                </p>
            </div>

            @if (request('category_id') || request('q') || request('type'))
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-medium transition self-start md:self-center">
                ✕ Clear Filters
            </a>
            @endif
        </div>
    </div>

    <!-- Main Catalog Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3">

                <!-- Category Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                    <a href="{{ route('catalog.index', array_filter(['q' => request('q'), 'type' => request('type')])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium whitespace-nowrap transition {{ !request('category_id') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        All Categories
                    </a>
                    @foreach ($categories as $cat)
                    <a href="{{ route('catalog.index', array_filter(['category_id' => $cat->id, 'q' => request('q'), 'type' => request('type')])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium whitespace-nowrap transition flex items-center gap-1.5 {{ request('category_id') == $cat->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>{{ $cat->name }}</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ request('category_id') == $cat->id ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $cat->products_count }}</span>
                    </a>
                    @endforeach
                </div>

                <!-- Type Filter Dropdown -->
                <form action="{{ route('catalog.index') }}" method="GET" class="w-full md:w-auto shrink-0">
                    @if (request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}" />
                    @endif
                    @if (request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}" />
                    @endif

                    <select name="type" onchange="this.form.submit()" class="w-full md:w-auto px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-medium text-slate-700 focus:outline-none">
                        <option value="">All Types</option>
                        <option value="finished_good" {{ request('type') === 'finished_good' ? 'selected' : '' }}>Finished Goods</option>
                        <option value="raw_material" {{ request('type') === 'raw_material' ? 'selected' : '' }}>Raw Materials</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($products as $product)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col justify-between group">

                <!-- Image -->
                <div class="relative h-48 bg-slate-100 overflow-hidden">
                    @if ($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        onerror="this.src='/images/bamboo_raw_poles.png'" />
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-slate-400 text-4xl">
                        🎋
                    </div>
                    @endif

                    @if ($product->category)
                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded bg-emerald-600 text-white font-medium text-[9px] uppercase">
                        {{ $product->category->name }}
                    </span>
                    @endif

                    <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-full text-[9px] font-medium {{ $product->stock_level > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $product->stock_level > 0 ? 'In Stock (' . $product->stock_level . ' ' . ($product->unit ?? 'pcs') . ')' : 'Out of Stock' }}
                    </span>
                </div>

                <!-- Details Body -->
                <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                    <div>
                        <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600 transition text-sm truncate">
                            <a href="{{ route('catalog.show', $product->id) }}">{{ $product->name }}</a>
                        </h3>
                        @if($product->description)
                        <p class="text-xs text-slate-500 font-medium line-clamp-1 mt-0.5">{{ $product->description }}</p>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-base font-semibold text-emerald-700">₹{{ number_format((float) $product->unit_price, 2) }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">/ {{ $product->unit ?? 'pcs' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('catalog.show', $product->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-medium text-xs transition">
                                Details
                            </a>

                            @php
                            $waMsg = rawurlencode("Hello Ashok Baans Store, I want to inquire about: " . $product->name);
                            @endphp
                            <a href="https://wa.me/919254998000?text={{ $waMsg }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs transition">
                                💬 Inquire
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 bg-white rounded-2xl border border-dashed border-slate-300 text-center text-xs text-slate-500">
                No products found matching your search.
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </main>

    <!-- Footer -->
    @include('partials.public-footer', ['categories' => $categories])
</body>

</html>