<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Thank You - Ashok Kumar Bans Store, Karnal</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #0b0f19 !important;
                color: #f8fafc !important;
            }
            .hero-glow-bg {
                background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.25) 0%, rgba(245, 158, 11, 0.1) 35%, rgba(11, 15, 25, 1) 75%);
            }
            .glass-card {
                background: rgba(26, 34, 52, 0.75) !important;
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
            }
            .btn-glow-emerald {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                color: #022c22 !important;
                font-weight: 800 !important;
                box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4) !important;
                transition: all 0.3s ease;
            }
            .btn-glow-emerald:hover {
                background: linear-gradient(135deg, #34d399 0%, #10b981 100%) !important;
                transform: translateY(-2px);
            }
            .btn-glass-secondary {
                background: rgba(255, 255, 255, 0.08) !important;
                color: #ffffff !important;
                border: 2px solid rgba(255, 255, 255, 0.2) !important;
                font-weight: 700 !important;
                transition: all 0.3s ease;
            }
            .btn-glass-secondary:hover {
                background: rgba(255, 255, 255, 0.18) !important;
                border-color: #10b981 !important;
                transform: translateY(-2px);
            }
            .text-emerald-glow {
                color: #34d399 !important;
            }
            .text-amber-glow {
                color: #fbbf24 !important;
            }
        </style>
    </head>
    <body class="bg-[#0b0f19] text-slate-100 min-h-screen flex flex-col justify-between hero-glow-bg selection:bg-emerald-500 selection:text-black">
        <!-- Top Announcement Bar -->
        <div class="w-full bg-gradient-to-r from-emerald-950 via-slate-900 to-amber-950 border-b border-emerald-500/30 py-2.5 px-4 text-center text-xs sm:text-sm font-bold flex items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500 text-slate-950">
                <span>🎋</span> ASHOK KUMAR BANS STORE
            </span>
            <span class="text-white font-bold">
                Karnal • Raw Bamboo Stock, Scaffolding & Ladders Ledger
            </span>
        </div>

        <!-- Main Header / Navigation -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3.5 group">
                <div class="w-12 h-12 p-3 rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-600 to-amber-500 flex items-center justify-center shadow-lg shadow-emerald-900/50 text-slate-950 font-extrabold text-2xl group-hover:scale-105 transition duration-300">
                    🎋
                </div>
                <div>
                    <span class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-1.5">
                        Ashok Kumar <span class="text-emerald-glow">Bans Store</span>
                    </span>
                    <span class="block text-xs text-amber-glow font-bold tracking-wide">
                        Ashok Kumar • Karnal, Haryana
                    </span>
                </div>
            </a>

            <!-- Navigation Links & Actions -->
            <div class="flex items-center gap-6">
                <nav class="hidden md:flex items-center gap-6 text-sm font-extrabold">
                    <a href="{{ route('home') }}" class="text-slate-300 hover:text-emerald-glow transition">Home</a>
                    <a href="{{ route('about') }}" class="text-slate-300 hover:text-emerald-glow transition">About Us</a>
                    <a href="{{ route('contact') }}" class="text-slate-300 hover:text-emerald-glow transition">Contact Us</a>
                </nav>

                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl btn-glow-emerald text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl btn-glass-secondary text-sm">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl btn-glow-emerald text-sm">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-16 flex items-center justify-center">
            <div class="glass-card p-10 sm:p-14 rounded-3xl text-center space-y-7 shadow-2xl border-2 border-emerald-500/50 w-full">
                <!-- Checkmark Icon -->
                <div class="w-24 h-24 rounded-full bg-emerald-500/20 border-4 border-emerald-400 text-emerald-glow flex items-center justify-center text-5xl mx-auto font-extrabold shadow-2xl animate-bounce">
                    ✓
                </div>

                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-500/20 border border-emerald-400 text-emerald-glow text-xs sm:text-sm font-extrabold shadow-md">
                    <span>Inquiry Submitted Successfully!</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold text-white">
                    Thank You for Reaching Out!
                </h1>

                <p class="text-slate-200 text-base sm:text-xl font-semibold leading-relaxed max-w-xl mx-auto">
                    We have received your inquiry at <strong>Ashok Kumar Bans Store, Karnal</strong>. Our store team will review your requirement and reach out to you on your contact number shortly.
                </p>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('home') }}" class="px-9 py-4 rounded-xl btn-glow-emerald text-base shadow-2xl inline-flex items-center gap-2.5">
                        <span>Return to Home</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-4 rounded-xl btn-glass-secondary text-base shadow-xl">
                        Submit Another Inquiry
                    </a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t-2 border-slate-800 bg-slate-950 py-8 text-center text-sm text-slate-200">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="font-bold text-white">© {{ date('Y') }} Ashok Kumar Bans Store, Karnal. All rights reserved.</span>
                <div class="flex items-center gap-6 text-sm font-bold text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-emerald-glow transition">Home</a>
                    <a href="{{ route('about') }}" class="hover:text-emerald-glow transition">About Us</a>
                    <a href="{{ route('contact') }}" class="hover:text-emerald-glow transition">Contact Us</a>
                </div>
                <span class="text-emerald-glow font-extrabold">Ashok Kumar • Karnal, Haryana</span>
            </div>
        </footer>
    </body>
</html>
