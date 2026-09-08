<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #060911 !important;
                color: #f8fafc !important;
                overflow-x: hidden;
            }
            .hero-bg-overlay {
                background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.28) 0%, rgba(245, 158, 11, 0.12) 35%, rgba(6, 9, 17, 0.94) 80%),
                            url('/images/bamboo_hero_bg.png') center/cover no-repeat;
            }
            .glow-card {
                background: linear-gradient(145deg, rgba(15, 23, 42, 0.95) 0%, rgba(6, 9, 17, 0.98) 100%);
                backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.18);
                box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.85);
            }
            .glow-card label, 
            .glow-card [data-flux-label],
            .glow-card [data-slot="label"] {
                color: #f8fafc !important;
                font-weight: 700 !important;
                font-size: 0.875rem !important;
            }
            .glow-card input[type="text"],
            .glow-card input[type="email"],
            .glow-card input[type="password"] {
                background-color: rgba(15, 23, 42, 0.9) !important;
                color: #ffffff !important;
                border: 1px solid rgba(255, 255, 255, 0.22) !important;
                border-radius: 0.75rem !important;
            }
            .glow-card input::placeholder {
                color: #94a3b8 !important;
            }
            .glow-card input:focus {
                border-color: #34d399 !important;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35) !important;
            }
            .glow-card p, 
            .glow-card span,
            .glow-card [data-flux-subheading],
            .glow-card [data-flux-description] {
                color: #cbd5e1 !important;
            }
            .glow-card h1, .glow-card h2, .glow-card h3 {
                color: #ffffff !important;
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
    <body class="bg-[#060911] text-slate-100 min-h-screen flex flex-col hero-bg-overlay selection:bg-emerald-500 selection:text-black">
        <!-- Top Announcement Bar -->
        <div class="w-full bg-gradient-to-r from-emerald-950 via-slate-950 to-amber-950 border-b border-emerald-500/30 py-2.5 px-4 text-center text-xs sm:text-sm font-black flex items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-slate-950 shadow-md">
                <span>🎋</span> ASHOK KUMAR BANS STORE
            </span>
            <span class="text-slate-100 font-bold">
                House No 2755, Opposite Gaushala Road, Janak Puri, Karnal, Haryana - 132001
            </span>
        </div>

        <!-- Main Header / Navigation -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap items-center justify-between gap-4 relative z-30">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 sm:gap-4 group" wire:navigate>
                <div class="w-10 h-10 sm:w-12 sm:h-12 p-2.5 sm:p-3 rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-600 to-amber-500 flex items-center justify-center shadow-xl shadow-emerald-950/80 text-slate-950 font-black text-xl sm:text-2xl group-hover:scale-105 transition duration-300">
                    🎋
                </div>
                <div>
                    <span class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-1.5">
                        Ashok Kumar <span class="gradient-text-emerald">Bans Store</span>
                    </span>
                    <span class="block text-[10px] sm:text-xs gradient-text-amber font-bold tracking-wide">
                        Karnal, Haryana • Direct Bamboo Merchant
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <div class="flex items-center flex-wrap gap-4 sm:gap-6">
                <nav class="flex items-center gap-3 sm:gap-6 text-xs sm:text-sm font-extrabold">
                    <a href="{{ route('home') }}" class="text-slate-300 hover:text-emerald-400 transition">Home</a>
                    <a href="{{ url('/#about') }}" class="text-slate-300 hover:text-emerald-400 transition">About Us</a>
                    <a href="{{ url('/#contact') }}" class="text-slate-300 hover:text-emerald-400 transition">Contact Us</a>
                </nav>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 sm:px-5 sm:py-2.5 rounded-full font-black text-xs sm:text-sm bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 shadow-lg shadow-emerald-500/20 transition transform hover:-translate-y-0.5 flex items-center gap-2" wire:navigate>
                        <span>📊</span> Open Khatabook Dashboard
                    </a>
                @else
                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="{{ route('login') }}" class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-full font-bold text-xs sm:text-sm {{ request()->routeIs('login') ? 'text-emerald-400 bg-slate-800/80 border border-emerald-500/30' : 'text-slate-200 hover:text-white hover:bg-slate-800/80' }} transition" wire:navigate>
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-1.5 sm:px-5 sm:py-2 rounded-full font-black text-xs sm:text-sm {{ request()->routeIs('register') ? 'bg-emerald-400 text-slate-950 shadow-lg' : 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950' }} transition transform hover:-translate-y-0.5" wire:navigate>
                                Register Store Account
                            </a>
                        @endif
                    </div>
                @endauth
            </div>
        </header>

        <!-- Form Card Section -->
        <div class="flex-1 flex items-center justify-center p-6 md:p-10 relative z-20">
            <div class="w-full max-w-md glow-card p-8 sm:p-10 rounded-3xl space-y-6">
                {{ $slot }}
            </div>
        </div>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-800 bg-slate-950 py-6 text-center text-sm text-slate-300 relative z-20">
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

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
