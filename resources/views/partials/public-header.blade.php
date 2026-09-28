@props(['active' => 'home', 'categories' => []])

@php
if (empty($categories) || !($categories instanceof \Illuminate\Support\Collection)) {
$categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->get();
}
@endphp

<!-- Top Announcement Marquee Bar -->
<div id="top-announcement-bar" class="w-full bg-slate-900 text-white text-xs sm:text-sm py-2.5 px-4 sm:px-8 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <div class="overflow-hidden flex-1 relative whitespace-nowrap">
            <div class="inline-flex items-center gap-8 font-medium text-slate-200">
                <span class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded bg-emerald-500 text-slate-950 font-semibold text-xs uppercase tracking-wider">DIRECT RATES</span>
                    <span class="font-medium">Wholesale Bamboo & Scaffolding Merchant • Karnal, Haryana</span>
                </span>
                <span class="text-slate-600">•</span>
                <span class="flex items-center gap-1.5 font-medium text-amber-300">
                    <span>📍 Janak Puri, Karnal</span>
                </span>
                <span class="text-slate-600">•</span>
                <span class="flex items-center gap-2 font-semibold text-emerald-400">
                    <span>📞 Direct Line: +91 92549 98000</span>
                </span>
            </div>
        </div>

        <button onclick="document.getElementById('top-announcement-bar').style.display='none'"
            class="text-slate-400 hover:text-white p-1 rounded shrink-0"
            aria-label="Close Announcement">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<!-- Main Sticky Header -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 sm:h-24 gap-4">

            <!-- Left Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3.5 shrink-0 group">
                <div class="h-12 w-12 sm:h-16 sm:w-16 rounded-2xl bg-slate-950 border-2 border-emerald-500/80 p-1.5 flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:border-amber-400 transition-all duration-300">
                    <img src="{{ asset('images/ak-emblem.png') }}"
                        alt="Ashok Kumar Baans Store Logo"
                        class="h-full w-full object-contain drop-shadow-md"
                        onerror="this.src='/images/ak-logo.png'" />
                </div>
                <div class="flex flex-col">
                    <span class="text-lg sm:text-2xl font-semibold text-slate-900 group-hover:text-emerald-600 transition tracking-tight">
                        ASHOK BAANS STORE
                    </span>
                    <span class="text-xs sm:text-xs font-medium text-emerald-600">Direct Bamboo & Scaffolding Yard • Karnal</span>
                </div>
            </a>

            <!-- Center Navigation Links -->
            <nav class="hidden lg:flex items-center gap-8 xl:gap-10 text-xs sm:text-sm font-medium uppercase tracking-wider">
                <a href="{{ route('home') }}" class="text-slate-800 hover:text-emerald-600 transition py-2 {{ $active === 'home' ? 'text-emerald-600 font-semibold border-b-3 border-emerald-600' : '' }}">
                    Home
                </a>

                <!-- Dropdown: Categories -->
                <div class="relative group">
                    <button type="button" class="flex items-center gap-1.5 text-slate-800 hover:text-emerald-600 py-2 transition uppercase font-medium">
                        <span>Categories</span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-0 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-3 hidden group-hover:block z-50">
                        @forelse ($categories as $cat)
                        <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="flex items-center justify-between px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-emerald-50 hover:text-emerald-700">
                            <span>{{ $cat->name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $cat->products_count }}</span>
                        </a>
                        @empty
                        <div class="px-5 py-2.5 text-xs text-slate-400">No categories</div>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('catalog.index') }}" class="text-slate-800 hover:text-emerald-600 transition py-2 {{ $active === 'catalog' ? 'text-emerald-600 font-semibold border-b-3 border-emerald-600' : '' }}">
                    Products Catalog
                </a>

                <a href="{{ route('about') }}" class="text-slate-800 hover:text-emerald-600 transition py-2 {{ $active === 'about' ? 'text-emerald-600 font-semibold border-b-3 border-emerald-600' : '' }}">
                    About
                </a>

                <a href="{{ route('contact') }}" class="text-slate-800 hover:text-emerald-600 transition py-2 {{ $active === 'contact' ? 'text-emerald-600 font-semibold border-b-3 border-emerald-600' : '' }}">
                    Contact
                </a>
            </nav>

            <!-- Right Search & Actions -->
            <div class="flex items-center gap-3">
                <form action="{{ route('catalog.index') }}" method="GET" class="hidden md:flex items-center bg-slate-100 focus-within:bg-white focus-within:ring-2 focus-within:ring-emerald-500 border border-slate-200 rounded-2xl px-4 py-2 transition">
                    <input type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search raw bamboo, ghodi..."
                        class="w-40 lg:w-52 bg-transparent text-sm font-medium text-slate-900 focus:outline-none placeholder-slate-400" />
                    <button type="submit" class="text-slate-500 hover:text-emerald-600 p-1" aria-label="Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                <a href="{{ route('contact') }}" class="p-3 rounded-2xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-medium transition relative flex items-center justify-center border border-emerald-200" title="Inquiry">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                </a>

                @if (Route::has('login'))
                @auth
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl bg-slate-900 text-white hover:bg-emerald-600 transition text-xs font-semibold">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition border border-slate-200/80" title="Login">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
                @endauth
                @endif

                <button onclick="toggleMobileDrawer()" type="button" class="lg:hidden p-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200" aria-label="Toggle Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobile-offcanvas-menu" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs">
        <div class="fixed inset-y-0 left-0 max-w-xs w-full bg-white shadow-2xl flex flex-col justify-between overflow-y-auto">
            <div class="p-5 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <span class="font-black text-slate-900 text-base">ASHOK BAANS STORE</span>
                    <button onclick="toggleMobileDrawer()" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" class="relative">
                    <input type="text" name="q" placeholder="Search products..." class="w-full pl-3 pr-10 py-2.5 rounded-xl bg-slate-100 text-xs font-medium border border-slate-200 text-slate-900 focus:outline-none" />
                    <button type="submit" class="absolute right-2 top-2.5 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                <nav class="space-y-2 font-bold text-sm text-slate-700">
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-xl hover:bg-slate-50">Home</a>
                    <a href="{{ route('catalog.index') }}" class="block px-3 py-2 rounded-xl hover:bg-slate-50">Products Catalog</a>
                    <a href="{{ route('about') }}" class="block px-3 py-2 rounded-xl hover:bg-slate-50">About</a>
                    <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-xl hover:bg-slate-50">Contact</a>
                </nav>
            </div>

            <div class="p-5 border-t border-slate-100 bg-slate-50">
                <a href="{{ route('contact') }}" class="w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2">
                    <span>Inquire Now</span>
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleMobileDrawer() {
        var menu = document.getElementById('mobile-offcanvas-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
</script>