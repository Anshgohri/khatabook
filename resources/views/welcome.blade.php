<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ashok Kumar Bans Store, Karnal - Premium Bamboo, Ghodi, Chaali & Siddhi Merchant</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] {
                display: none !important;
            }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #060911 !important;
                color: #f8fafc !important;
                overflow-x: hidden;
            }
            .hero-bg-overlay {
                background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.28) 0%, rgba(245, 158, 11, 0.12) 35%, rgba(6, 9, 17, 0.94) 80%),
                            url('/images/bamboo_hero_bg.png') center/cover no-repeat;
            }
            .glow-card {
                background: linear-gradient(145deg, rgba(20, 29, 47, 0.85) 0%, rgba(11, 16, 28, 0.95) 100%);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.12);
                box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.7);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .glow-card:hover {
                border-color: rgba(16, 185, 129, 0.6);
                box-shadow: 0 25px 60px -15px rgba(16, 185, 129, 0.35);
            }
            .btn-emerald-glow {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                color: #022c22 !important;
                font-weight: 800 !important;
                transition: all 0.3s ease;
                box-shadow: 0 10px 30px -5px rgba(16, 185, 129, 0.5) !important;
            }
            .btn-emerald-glow:hover {
                background: linear-gradient(135deg, #34d399 0%, #10b981 100%) !important;
                transform: translateY(-2px);
                box-shadow: 0 18px 35px -5px rgba(16, 185, 129, 0.7) !important;
            }
            .btn-glass-secondary {
                background: rgba(255, 255, 255, 0.08) !important;
                backdrop-filter: blur(16px);
                color: #ffffff !important;
                border: 1px solid rgba(255, 255, 255, 0.22) !important;
                font-weight: 700 !important;
                transition: all 0.3s ease;
            }
            .btn-glass-secondary:hover {
                background: rgba(255, 255, 255, 0.18) !important;
                border-color: #10b981 !important;
                transform: translateY(-2px);
            }
            .gradient-text-emerald {
                background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #fbbf24 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .gradient-text-amber {
                background: linear-gradient(135deg, #fef08a 0%, #fbbf24 50%, #f59e0b 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .shop-slide {
                display: none;
                animation: fadeInSlide 0.5s ease-in-out forwards;
            }
            .shop-slide.active {
                display: grid !important;
            }
            @keyframes fadeInSlide {
                from { opacity: 0; transform: scale(0.98); }
                to { opacity: 1; transform: scale(1); }
            }
        </style>
    </head>
    <body class="bg-[#060911] text-slate-100 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-black">

        <!-- Top Announcement Bar -->
        <div class="w-full bg-gradient-to-r from-emerald-950 via-slate-950 to-amber-950 border-b border-emerald-500/30 py-2.5 px-4 text-center text-xs sm:text-sm font-black flex items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-slate-950 shadow-md animate-pulse">
                <span>🎋</span> ASHOK KUMAR BANS STORE
            </span>
            <span class="text-slate-100 font-bold">
                Karnal, Haryana • Wholesale & Retail Direct Bamboo Merchant • 15ft, 20ft, 25ft Baans, Ghodi, Chaali & Siddhi
            </span>
        </div>

        <!-- Main Header / Navigation -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between relative z-30">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-4 group">
                <div class="w-13 h-13 p-3 rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-600 to-amber-500 flex items-center justify-center shadow-xl shadow-emerald-950/80 text-slate-950 font-black text-2xl group-hover:scale-105 transition duration-300">
                    🎋
                </div>
                <div>
                    <span class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-1.5">
                        Ashok Kumar <span class="gradient-text-emerald">Bans Store</span>
                    </span>
                    <span class="block text-xs gradient-text-amber font-bold tracking-wide">
                        Ashok Kumar • Karnal, Haryana
                    </span>
                </div>
            </a>

            <!-- Navigation Links & Single Header Button -->
            <div class="flex items-center flex-wrap gap-4 sm:gap-6">
                <nav class="flex items-center gap-3 sm:gap-6 text-xs sm:text-sm font-extrabold">
                    <a href="{{ route('home') }}" class="text-emerald-400 font-extrabold underline underline-offset-8 decoration-emerald-500 decoration-2">Home</a>
                    <a href="{{ route('about') }}" class="text-slate-300 hover:text-emerald-400 transition">About Us</a>
                    <a href="{{ route('contact') }}" class="text-slate-300 hover:text-emerald-400 transition">Contact Us</a>
                </nav>

                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl btn-emerald-glow text-sm flex items-center gap-2">
                                <span>Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl btn-glass-secondary text-sm">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl btn-emerald-glow text-sm">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Real Bamboo Shop Hero Section -->
        <section class="relative min-h-[75vh] flex items-center justify-center hero-bg-overlay px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="max-w-5xl mx-auto text-center space-y-8 relative z-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-slate-950/90 border-2 border-emerald-500/50 text-emerald-400 text-xs sm:text-sm font-black shadow-2xl backdrop-blur-md">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>DIRECT BAMBOO MERCHANT IN KARNAL, HARYANA</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight">
                    Wholesale Raw Bamboo, <span class="gradient-text-emerald">Ghodi, Chaali & Siddhi</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-slate-200 text-lg sm:text-2xl font-semibold max-w-3xl mx-auto leading-relaxed drop-shadow-md">
                    Ashok Kumar Bans Store in Karnal. Direct timber yard supplier of 15ft, 20ft & 25ft raw bamboo poles, heavy-duty scaffolding Ghodi trestles, woven Chaali platforms, and sturdy Siddhi ladders.
                </p>

                <!-- Hero Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-5 pt-2">
                    <a href="#products-showcase" class="px-9 py-4 rounded-xl btn-emerald-glow text-base flex items-center gap-3 shadow-2xl">
                        <span>Explore Real Products</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-4 rounded-xl btn-glass-secondary text-base shadow-xl">
                        Contact Shop Owner
                    </a>
                </div>

                <!-- Quick Highlights Stat Bar -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto pt-6">
                    <div class="glow-card p-4 rounded-2xl text-center">
                        <div class="text-2xl mb-1">🪵</div>
                        <div class="text-base font-black text-white">15ft / 20ft / 25ft</div>
                        <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Raw Baans Sizes</div>
                    </div>
                    <div class="glow-card p-4 rounded-2xl text-center">
                        <div class="text-2xl mb-1">🪜</div>
                        <div class="text-base font-black text-white">Scaffolding Ghodi</div>
                        <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">A-Frame Trestles</div>
                    </div>
                    <div class="glow-card p-4 rounded-2xl text-center">
                        <div class="text-2xl mb-1">🧱</div>
                        <div class="text-base font-black text-white">Bamboo Chaali</div>
                        <div class="text-xs font-bold text-sky-400 uppercase tracking-wider">Woven Platforms</div>
                    </div>
                    <div class="glow-card p-4 rounded-2xl text-center">
                        <div class="text-2xl mb-1">🧗</div>
                        <div class="text-base font-black text-white">Bamboo Siddhi</div>
                        <div class="text-xs font-bold text-purple-400 uppercase tracking-wider">Site Ladders</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Big 1-by-1 Interactive Product Showcase Slider (Full Width) -->
        <section id="products-showcase" class="w-full py-16 px-4 sm:px-6 lg:px-12">

            <div class="text-center max-w-4xl mx-auto mb-10 space-y-3">
                <span class="inline-block px-4 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 text-xs font-black uppercase tracking-widest">
                    REAL SHOP STORE GALLERY
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Store Products (Ghodi, Chaali, Siddhi & Baans)</h2>
                <p class="text-slate-300 text-base font-semibold">Real high-grade bamboo materials ready for immediate loading at our Karnal store yard.</p>
            </div>

            <!-- Big Slider Container Card (Full Width) -->
            <div class="relative w-full glow-card rounded-3xl overflow-hidden shadow-2xl border-2 border-emerald-500/30 min-h-[600px] flex flex-col justify-between">

                <!-- SLIDE 0: Bamboo Scaffolding Ghodi -->
                <div id="shop-slide-0" class="shop-slide active grid-cols-1 lg:grid-cols-12 min-h-[600px]">
                    <!-- Big Image (Spans 8 cols on desktop for maximum width) -->
                    <div class="lg:col-span-8 h-96 lg:h-auto relative overflow-hidden bg-slate-950">
                        <img src="/images/bamboo_ghodi.png" alt="Bamboo Scaffolding Ghodi" class="w-full h-full object-cover">
                        <div class="absolute top-6 left-6 px-4 py-1.5 rounded-full bg-amber-500 text-slate-950 text-xs font-black shadow-xl">
                            PRODUCT 1 OF 4 • SCAFFOLDING GHODI
                        </div>
                    </div>
                    <!-- Details (Spans 4 cols) -->
                    <div class="lg:col-span-4 p-8 lg:p-12 flex flex-col justify-between space-y-6 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950">
                        <div class="space-y-5">
                            <span class="text-xs font-black uppercase tracking-wider text-amber-400">Scaffolding Equipment</span>
                            <h3 class="text-3xl sm:text-4xl font-black text-white leading-tight">Bamboo Scaffolding Ghodi (Trestles)</h3>
                            <p class="text-slate-200 text-base leading-relaxed font-semibold">
                                Heavy-duty A-frame bamboo trestle structures (Ghodi) handcrafted by master bamboo artisans in Karnal. Engineered for heavy building scaffolding support, plastering, and tall structure masonry.
                            </p>
                            <ul class="space-y-2.5 text-sm text-slate-200 font-extrabold pt-2">
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Hand-bound high-tensile bamboo trestles</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Custom heights for construction sites</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Available for bulk contractor loading</span>
                                </li>
                            </ul>
                        </div>
                        <div class="pt-6 border-t border-slate-700/80 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-slate-400 font-bold">Category</span>
                                <span class="text-base font-black text-amber-400">Scaffolding Ghodi</span>
                            </div>
                            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl btn-emerald-glow text-sm">Inquire Shop Rate</a>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 1: Bamboo Chaali Platforms -->
                <div id="shop-slide-1" class="shop-slide grid-cols-1 lg:grid-cols-12 min-h-[600px]">
                    <!-- Big Image -->
                    <div class="lg:col-span-8 h-96 lg:h-auto relative overflow-hidden bg-slate-950">
                        <img src="/images/bamboo_chaali.png" alt="Woven Bamboo Chaali Work Platforms" class="w-full h-full object-cover">
                        <div class="absolute top-6 left-6 px-4 py-1.5 rounded-full bg-sky-500 text-slate-950 text-xs font-black shadow-xl">
                            PRODUCT 2 OF 4 • BAMBOO CHAALI
                        </div>
                    </div>
                    <!-- Details -->
                    <div class="lg:col-span-4 p-8 lg:p-12 flex flex-col justify-between space-y-6 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950">
                        <div class="space-y-5">
                            <span class="text-xs font-black uppercase tracking-wider text-sky-400">Scaffolding Platforms</span>
                            <h3 class="text-3xl sm:text-4xl font-black text-white leading-tight">Woven Bamboo Chaali (Platform Mats)</h3>
                            <p class="text-slate-200 text-base leading-relaxed font-semibold">
                                Tightly woven, high-strength bamboo platform mats (Chaali) essential for safe high-rise scaffolding walks, worker footing, and construction plastering platforms.
                            </p>
                            <ul class="space-y-2.5 text-sm text-slate-200 font-extrabold pt-2">
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Tightly woven thick split bamboo mats</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Standard sizes for building scaffolding</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Heavy load-bearing capacity</span>
                                </li>
                            </ul>
                        </div>
                        <div class="pt-6 border-t border-slate-700/80 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-slate-400 font-bold">Category</span>
                                <span class="text-base font-black text-sky-400">Bamboo Chaali</span>
                            </div>
                            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl btn-emerald-glow text-sm">Inquire Shop Rate</a>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: Bamboo Siddhi Ladders -->
                <div id="shop-slide-2" class="shop-slide grid-cols-1 lg:grid-cols-12 min-h-[600px]">
                    <!-- Big Image -->
                    <div class="lg:col-span-8 h-96 lg:h-auto relative overflow-hidden bg-slate-950">
                        <img src="/images/bamboo_siddhi.png" alt="Bamboo Siddhi Construction Ladders" class="w-full h-full object-cover">
                        <div class="absolute top-6 left-6 px-4 py-1.5 rounded-full bg-purple-500 text-slate-950 text-xs font-black shadow-xl">
                            PRODUCT 3 OF 4 • BAMBOO SIDDHI
                        </div>
                    </div>
                    <!-- Details -->
                    <div class="lg:col-span-4 p-8 lg:p-12 flex flex-col justify-between space-y-6 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950">
                        <div class="space-y-5">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-400">Construction Ladders</span>
                            <h3 class="text-3xl sm:text-4xl font-black text-white leading-tight">Bamboo Siddhi (Ladders)</h3>
                            <p class="text-slate-200 text-base leading-relaxed font-semibold">
                                Extra sturdy single and double reach bamboo ladders (Siddhi) engineered for painters, electricians, masons, and construction site height reach.
                            </p>
                            <ul class="space-y-2.5 text-sm text-slate-200 font-extrabold pt-2">
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Single & Double extended height options</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Reinforced rungs for worker safety</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Lightweight & durable construction</span>
                                </li>
                            </ul>
                        </div>
                        <div class="pt-6 border-t border-slate-700/80 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-slate-400 font-bold">Category</span>
                                <span class="text-base font-black text-purple-400">Bamboo Siddhi</span>
                            </div>
                            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl btn-emerald-glow text-sm">Inquire Shop Rate</a>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3: Raw Bamboo Poles -->
                <div id="shop-slide-3" class="shop-slide grid-cols-1 lg:grid-cols-12 min-h-[600px]">
                    <!-- Big Image -->
                    <div class="lg:col-span-8 h-96 lg:h-auto relative overflow-hidden bg-slate-950">
                        <img src="/images/bamboo_raw_poles.png" alt="Raw Bamboo Poles 15ft 20ft 25ft" class="w-full h-full object-cover">
                        <div class="absolute top-6 left-6 px-4 py-1.5 rounded-full bg-emerald-500 text-slate-950 text-xs font-black shadow-xl">
                            PRODUCT 4 OF 4 • RAW BAMBOO
                        </div>
                    </div>
                    <!-- Details -->
                    <div class="lg:col-span-4 p-8 lg:p-12 flex flex-col justify-between space-y-6 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950">
                        <div class="space-y-5">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-400">Raw Timber Supply</span>
                            <h3 class="text-3xl sm:text-4xl font-black text-white leading-tight">Raw Bamboo Poles (15ft, 20ft, 25ft)</h3>
                            <p class="text-slate-200 text-base leading-relaxed font-semibold">
                                Premium Grade-A raw bamboo poles (Baans) stocked in standard 15 feet, 20 feet, and 25 feet sizes. Sourced directly for builders, scaffolding contractors, and agricultural usage.
                            </p>
                            <ul class="space-y-2.5 text-sm text-slate-200 font-extrabold pt-2">
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>15ft, 20ft, 25ft length bundles</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Thick-walled high strength poles</span>
                                </li>
                                <li class="flex items-center gap-2.5 text-emerald-400">
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">✓</span>
                                    <span>Direct truckload dispatch from Karnal</span>
                                </li>
                            </ul>
                        </div>
                        <div class="pt-6 border-t border-slate-700/80 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-slate-400 font-bold">Category</span>
                                <span class="text-base font-black text-emerald-400">Raw Bamboo Poles</span>
                            </div>
                            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl btn-emerald-glow text-sm">Inquire Shop Rate</a>
                        </div>
                    </div>
                </div>

                <!-- Navigation Controls Bar -->
                <div class="p-6 bg-slate-950/90 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 z-20">
                    <!-- Left / Right Arrow Controls -->
                    <div class="flex items-center gap-3">
                        <button onclick="changeShopSlide(-1)" class="px-4 py-2.5 rounded-xl btn-glass-secondary hover:text-emerald-400 flex items-center gap-2 text-sm font-extrabold" aria-label="Previous Slide">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            <span>Previous</span>
                        </button>
                        <button onclick="changeShopSlide(1)" class="px-4 py-2.5 rounded-xl btn-glass-secondary hover:text-emerald-400 flex items-center gap-2 text-sm font-extrabold" aria-label="Next Slide">
                            <span>Next Product</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <!-- Interactive Product Selector Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto max-w-full">
                        <button id="pill-0" onclick="showShopSlide(0)" class="pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all bg-amber-400 text-slate-950 shadow-md">1. Ghodi</button>
                        <button id="pill-1" onclick="showShopSlide(1)" class="pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all bg-slate-800 text-slate-300 hover:bg-slate-700">2. Chaali</button>
                        <button id="pill-2" onclick="showShopSlide(2)" class="pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all bg-slate-800 text-slate-300 hover:bg-slate-700">3. Siddhi</button>
                        <button id="pill-3" onclick="showShopSlide(3)" class="pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all bg-slate-800 text-slate-300 hover:bg-slate-700">4. Raw Baans</button>
                    </div>
                </div>

            </div>
        </section>

        <!-- Store Location & Direct Contact Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full mb-20">
            <div class="glow-card p-8 lg:p-14 rounded-3xl flex flex-col lg:flex-row items-center justify-between gap-8 bg-gradient-to-r from-emerald-950/90 via-slate-900 to-slate-950 border-2 border-emerald-500/40 shadow-2xl">
                <div class="space-y-4 text-center lg:text-left max-w-2xl">
                    <span class="px-4 py-1.5 rounded-full bg-amber-400 text-slate-950 text-xs font-black">STORE LOCATION & ORDERS</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">Ashok Kumar Bans Store, Karnal</h2>
                    <p class="text-slate-200 text-base font-semibold leading-relaxed">
                        We welcome wholesale contractor bulk bookings, retail walk-in purchases, and local transport truck delivery across Karnal and nearby Haryana.
                    </p>
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs font-extrabold text-emerald-400 pt-2">
                        <span>✓ Raw Baans (15ft, 20ft, 25ft)</span>
                        <span>✓ Scaffolding Ghodi</span>
                        <span>✓ Bamboo Chaali</span>
                        <span>✓ Siddhi Ladders</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                    <a href="{{ route('contact') }}" class="px-9 py-4 rounded-xl btn-emerald-glow text-base text-center shadow-xl">
                        Contact Shop Now
                    </a>
                    <a href="{{ route('about') }}" class="px-9 py-4 rounded-xl btn-glass-secondary text-base text-center shadow-xl">
                        Learn About Our Yard
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-800 bg-slate-950 py-8 text-center text-sm text-slate-300 mt-auto">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="font-bold text-white">© {{ date('Y') }} Ashok Kumar Bans Store, Karnal. All rights reserved.</span>
                <div class="flex items-center gap-6 text-sm font-bold text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-emerald-400 transition">Home</a>
                    <a href="{{ route('about') }}" class="hover:text-emerald-400 transition">About Us</a>
                    <a href="{{ route('contact') }}" class="hover:text-emerald-400 transition">Contact Us</a>
                </div>
                <span class="text-emerald-400 font-extrabold">Ashok Kumar • Karnal, Haryana</span>
            </div>
        </footer>

        <!-- Zero-Dependency JavaScript Slider Controller -->
        <script>
            let currentShopSlideIndex = 0;
            const totalShopSlides = 4;
            const pillColors = ['bg-amber-400 text-slate-950 shadow-md', 'bg-sky-400 text-slate-950 shadow-md', 'bg-purple-400 text-slate-950 shadow-md', 'bg-emerald-400 text-slate-950 shadow-md'];

            function showShopSlide(index) {
                currentShopSlideIndex = (index + totalShopSlides) % totalShopSlides;
                for (let i = 0; i < totalShopSlides; i++) {
                    const slide = document.getElementById('shop-slide-' + i);
                    const pill = document.getElementById('pill-' + i);
                    if (i === currentShopSlideIndex) {
                        slide.classList.add('active');
                        pill.className = 'pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all ' + pillColors[i];
                    } else {
                        slide.classList.remove('active');
                        pill.className = 'pill-btn px-3.5 py-1.5 rounded-lg text-xs font-black transition-all bg-slate-800 text-slate-300 hover:bg-slate-700';
                    }
                }
            }

            function changeShopSlide(direction) {
                showShopSlide(currentShopSlideIndex + direction);
            }

            // Auto-rotate every 6 seconds
            setInterval(() => {
                changeShopSlide(1);
            }, 6000);
        </script>
    </body>
</html>
