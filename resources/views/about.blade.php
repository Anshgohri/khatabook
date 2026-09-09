<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>About Us - Ashok Kumar Bans Store, Karnal</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #060911 !important;
                color: #f8fafc !important;
                overflow-x: hidden;
            }
            .hero-bg-overlay {
                background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.25) 0%, rgba(245, 158, 11, 0.1) 35%, rgba(6, 9, 17, 0.94) 80%),
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
        </style>
    </head>
    <body class="bg-[#060911] text-slate-100 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-black">

        <!-- Top Announcement Bar -->
        <div class="w-full bg-gradient-to-r from-emerald-950 via-slate-950 to-amber-950 border-b border-emerald-500/30 py-2.5 px-4 text-center text-xs sm:text-sm font-black flex items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-slate-950 shadow-md">
                <span>🎋</span> ASHOK KUMAR BANS STORE
            </span>
            <span class="text-slate-100 font-bold">
                Karnal, Haryana • Raw Bamboo Stock, Scaffolding Ghodi, Chaali & Siddhi Merchant
            </span>
        </div>

        <!-- Main Header / Navigation -->
        @include('partials.public-header', ['active' => 'about'])

        <!-- Hero Section -->
        <section class="relative py-16 lg:py-24 hero-bg-overlay px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-5">
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-slate-950/90 border-2 border-emerald-500/40 text-emerald-400 text-xs sm:text-sm font-black shadow-xl">
                    <span>TRUSTED BAMBOO MERCHANTS • KARNAL, HARYANA</span>
                </div>
                <h1 class="text-4xl sm:text-6xl font-black text-white">About <span class="gradient-text-emerald">Ashok Kumar Bans Store</span></h1>
                <p class="text-slate-200 text-base sm:text-xl font-semibold leading-relaxed">
                    Providing premium raw bamboo poles and custom construction scaffolding equipment for building contractors and retail buyers across Haryana.
                </p>
            </div>
        </section>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
                <div class="glow-card p-8 sm:p-10 rounded-3xl lg:col-span-2 space-y-6 shadow-2xl">
                    <h2 class="text-3xl font-black text-white">Our Business Story</h2>
                    <p class="text-slate-200 text-base sm:text-lg leading-relaxed font-semibold">
                        <strong>Ashok Kumar Bans Store</strong> is a premier bamboo merchant located in <strong>Karnal, Haryana</strong>, owned and operated under <strong>Ashok Kumar</strong>. We specialize in sourcing top-grade raw bamboos in standard 15 feet, 20 feet, and 25 feet lengths.
                    </p>
                    <p class="text-slate-200 text-base sm:text-lg leading-relaxed font-semibold">
                        In addition to raw bamboo supply, we run a dedicated processing unit where skilled craftsmen manufacture construction essential scaffolding equipment:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-700/80">
                            <div class="text-3xl mb-2">🪜</div>
                            <h4 class="font-black text-white text-base">Scaffolding Ghodi</h4>
                            <p class="text-xs text-slate-300 mt-1 font-semibold">Heavy-duty bamboo trestles for sturdy building support.</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-700/80">
                            <div class="text-3xl mb-2">🧱</div>
                            <h4 class="font-black text-white text-base">Bamboo Chaali</h4>
                            <p class="text-xs text-slate-300 mt-1 font-semibold">Woven work platforms for safe high-rise construction.</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-700/80">
                            <div class="text-3xl mb-2">🧗</div>
                            <h4 class="font-black text-white text-base">Bamboo Siddhi</h4>
                            <p class="text-xs text-slate-300 mt-1 font-semibold">Single and double ladders crafted in all reach heights.</p>
                        </div>
                    </div>
                </div>

                <!-- Business Highlights Sidebar -->
                <div class="space-y-6">
                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="text-4xl">📍</div>
                        <h3 class="text-xl font-black text-white">Location</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">Ashok Kumar Bans Store<br>Karnal, Haryana - 132001</p>
                    </div>

                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="text-4xl">💼</div>
                        <h3 class="text-xl font-black text-white">Wholesale & Retail</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">Bulk contractor prices and flexible retail billing for market buyers.</p>
                    </div>

                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="text-4xl">⚡</div>
                        <h3 class="text-xl font-black text-white">Digital Ledger</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">100% transparent digital system tracking stock levels and customer credit.</p>
                    </div>
                </div>
            </div>

            <!-- CTA Box -->
            <div class="glow-card p-10 lg:p-12 rounded-3xl text-center space-y-5 bg-gradient-to-r from-emerald-950 via-slate-900 to-slate-950 border-2 border-emerald-500/50 shadow-2xl">
                <h2 class="text-3xl sm:text-4xl font-black text-white">Need Raw Bamboo or Construction Scaffolding?</h2>
                <p class="text-slate-200 text-base font-semibold max-w-xl mx-auto">Get in touch with us today for bulk wholesale quotes, custom Ghodi/Chaali sizes, or store visits in Karnal.</p>
                <div class="pt-2">
                    <a href="{{ route('contact') }}" class="px-9 py-4 rounded-xl btn-emerald-glow text-base inline-block shadow-xl">Contact Ashok Kumar Bans Store</a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-800 bg-slate-950 py-8 text-center text-sm text-slate-300">
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
    </body>
</html>
