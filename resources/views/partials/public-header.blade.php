@props(['active' => 'home', 'categories' => []])

@php
    if (empty($categories) || !($categories instanceof \Illuminate\Support\Collection)) {
        $categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->get();
    }
@endphp

<!-- Top Announcement Bar -->
<div class="w-full bg-gradient-to-r from-emerald-900 via-emerald-800 to-slate-900 text-white text-xs py-2 px-4 shadow-sm border-b border-emerald-700/50">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
        <div class="flex items-center gap-2 font-medium">
            <span class="bg-emerald-500 text-slate-950 px-2 py-0.5 rounded font-black text-[10px] tracking-wider uppercase">DIRECT MERCHANT</span>
            <span class="truncate">📍 Janak Puri, Opp. Gaushala Road, Karnal, Haryana - 132001</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-bold">
            <a href="tel:+919254998000" class="hover:text-amber-300 transition flex items-center gap-1">
                <span>📞</span> +91 92549 98000
            </a>
            <span class="text-emerald-400/60 hidden md:inline">•</span>
            <a href="tel:+919255523276" class="hover:text-amber-300 transition hidden md:flex items-center gap-1">
                <span>📱</span> +91 92555 23276
            </a>
            <span class="text-emerald-400/60 hidden lg:inline">•</span>
            <span class="text-emerald-200 hidden lg:inline font-semibold">⏰ 6:00 AM – 10:00 PM</span>
        </div>
    </div>
</div>

<!-- Main Sticky Header Header (2-Row Spacious Layout) -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
    
    <!-- Header Tier 1: Logo + Deliver To + Search Bar + Actions -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between gap-6">
            
            <!-- Left: Brand Logo & Title -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group" aria-label="Ashok Kumar Baans Store">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-slate-900 border-2 border-emerald-500/40 p-1 shadow-sm group-hover:border-emerald-500 transition shrink-0">
                    <img src="{{ asset('images/ak-emblem.png') }}" alt="AK Emblem" class="h-full w-full object-contain" />
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-emerald-600 transition tracking-tight">
                            ASHOK KUMAR
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 border border-emerald-300 text-emerald-800 font-extrabold text-[10px] uppercase hidden sm:inline-block">
                            STORE
                        </span>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                        <span>BAANS STORE</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-amber-600 font-medium">Direct Bamboo Merchant</span>
                    </span>
                </div>
            </a>

            <!-- Center: Search Bar & Deliver To (Desktop) -->
            <div class="hidden lg:flex items-center gap-3 flex-1 max-w-2xl mx-4">
                <!-- Location Indicator -->
                <div class="relative shrink-0">
                    <div class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 cursor-default shadow-xs">
                        <span class="text-emerald-600">📍</span>
                        <span class="truncate">Deliver to Karnal & Haryana</span>
                    </div>
                </div>

                <!-- Product Search Form -->
                <form action="{{ route('catalog.index') }}" method="GET" class="flex-1 relative flex items-center bg-slate-50 border border-slate-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500 transition shadow-xs">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search raw bamboo, ghodi, chaali, siddhi..." 
                           class="w-full pl-4 pr-3 py-2 text-xs font-medium text-slate-900 placeholder-slate-400 bg-transparent border-0 focus:outline-none focus:ring-0" />
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs flex items-center justify-center gap-1.5 transition shrink-0 shadow-xs" aria-label="Search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Search</span>
                    </button>
                </form>
            </div>

            <!-- Right: Inquiry Cart & Dashboard Action -->
            <div class="hidden lg:flex items-center gap-4 shrink-0">
                <a href="{{ route('contact') }}" title="Inquiry Cart" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-600 font-bold text-xs transition relative flex items-center gap-2 border border-slate-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                    <span>Inquiry Cart</span>
                    <span class="bg-amber-500 text-slate-950 font-black text-[10px] px-1.5 py-0.2 rounded-full">!</span>
                </a>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition flex items-center gap-1.5">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-sm transition">
                            Login to Admin
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Mobile Right Controls (Search + Hamburger) -->
            <div class="flex lg:hidden items-center gap-2">
                <a href="{{ route('contact') }}" class="p-2 rounded-lg bg-slate-100 text-slate-700 relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                </a>
                <button onclick="toggleMobileNavbar(event)" type="button" class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:text-emerald-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Quick Search Form -->
        <div class="lg:hidden mt-3 pt-2 border-t border-slate-100">
            <form action="{{ route('catalog.index') }}" method="GET" class="relative flex items-center">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Search products..." 
                       class="w-full pl-4 pr-20 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                <button type="submit" class="absolute right-1 px-3 py-1.5 bg-emerald-600 text-white rounded-lg font-bold text-xs">
                    Search
                </button>
            </form>
        </div>
    </div>

    <!-- Header Tier 2: Navigation Bar with Categories Dropdown (Desktop Only) -->
    <div class="hidden lg:block bg-slate-50/90 border-t border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between">
            
            <!-- Left: All Categories Dropdown (Zero-Dependency Vanilla JS Toggle) -->
            <div class="relative">
                <button id="cat-dropdown-btn" 
                        onclick="toggleCategoriesDropdown(event)" 
                        type="button" 
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <span>☰ All Categories</span>
                    <svg id="cat-dropdown-arrow" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <!-- Categories Dropdown Menu -->
                <div id="cat-dropdown-menu" 
                     class="hidden absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 py-2.5 z-50 transition-all duration-200">
                    <div class="px-4 py-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        Admin Categories
                    </div>
                    @forelse ($categories as $cat)
                        <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="flex items-center justify-between px-4 py-2.5 text-xs font-extrabold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span>{{ $cat->name }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-black border border-slate-200">{{ $cat->products_count }}</span>
                        </a>
                    @empty
                        <div class="px-4 py-3 text-xs text-slate-400">No categories added yet</div>
                    @endforelse
                    <div class="border-t border-slate-100 mt-2 pt-2 px-4">
                        <a href="{{ route('catalog.index') }}" class="text-[11px] font-extrabold text-emerald-600 hover:underline flex items-center justify-between">
                            <span>View Full Catalog</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right / Center: Navigation Links -->
            <nav class="flex items-center gap-7 text-xs font-extrabold">
                <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-emerald-600 font-black border-b-2 border-emerald-600 pb-0.5' : 'text-slate-600 hover:text-emerald-600' }} transition">Home</a>
                <a href="{{ route('catalog.index') }}" class="{{ $active === 'catalog' ? 'text-emerald-600 font-black border-b-2 border-emerald-600 pb-0.5' : 'text-slate-600 hover:text-emerald-600' }} transition">All Products Catalog</a>
                <a href="{{ route('about') }}" class="{{ $active === 'about' ? 'text-emerald-600 font-black border-b-2 border-emerald-600 pb-0.5' : 'text-slate-600 hover:text-emerald-600' }} transition">About Us</a>
                <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? 'text-emerald-600 font-black border-b-2 border-emerald-600 pb-0.5' : 'text-slate-600 hover:text-emerald-600' }} transition">Contact Us</a>
                <a href="{{ route('home') }}#contact" class="text-amber-600 hover:text-amber-700 transition flex items-center gap-1">
                    <span>⚡ Wholesale Rates</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobile-header-drawer" 
         class="hidden lg:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-3">
        <nav class="flex flex-col gap-2 font-bold text-sm text-slate-700">
            <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg {{ $active === 'home' ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'hover:bg-slate-50' }}">Home</a>
            <a href="{{ route('catalog.index') }}" class="px-3 py-2 rounded-lg {{ $active === 'catalog' ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'hover:bg-slate-50' }}">All Products Catalog</a>
            
            <!-- Mobile Categories Accordion -->
            <div class="px-3 py-2 rounded-lg bg-slate-50 space-y-2">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-extrabold">Product Categories</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                    @foreach ($categories as $cat)
                        <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="px-2.5 py-1.5 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:text-emerald-600 truncate">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('about') }}" class="px-3 py-2 rounded-lg {{ $active === 'about' ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'hover:bg-slate-50' }}">About Us</a>
            <a href="{{ route('contact') }}" class="px-3 py-2 rounded-lg {{ $active === 'contact' ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'hover:bg-slate-50' }}">Contact Us</a>
        </nav>

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-lg bg-emerald-600 text-white font-extrabold text-xs">
                        Go to Admin Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-lg bg-slate-900 text-white font-extrabold text-xs">
                        Login to Admin
                    </a>
                @endauth
            @endif
        </div>
    </div>
</header>

<!-- Zero-Dependency JavaScript Handlers for Header Dropdowns -->
<script>
    function toggleCategoriesDropdown(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var menu = document.getElementById('cat-dropdown-menu');
        var arrow = document.getElementById('cat-dropdown-arrow');
        if (menu) {
            var isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                if (arrow) arrow.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }
    }

    function toggleMobileNavbar(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var drawer = document.getElementById('mobile-header-drawer');
        if (drawer) {
            drawer.classList.toggle('hidden');
        }
    }

    // Close dropdown menu when clicking anywhere outside
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('cat-dropdown-menu');
        var btn = document.getElementById('cat-dropdown-btn');
        var arrow = document.getElementById('cat-dropdown-arrow');
        if (menu && !menu.classList.contains('hidden')) {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }
    });
</script>