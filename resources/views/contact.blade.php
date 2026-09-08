<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Contact Us - Ashok Kumar Bans Store, Karnal</title>

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
            .form-input-glass {
                background: rgba(11, 16, 28, 0.95) !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
                color: #ffffff !important;
                font-weight: 600 !important;
                transition: all 0.25s ease;
            }
            .form-input-glass:focus {
                border-color: #10b981 !important;
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.35) !important;
                outline: none !important;
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
                Karnal, Haryana • Direct Wholesale & Retail Bamboo Merchant Inquiry
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
                    <a href="{{ route('home') }}" class="text-slate-300 hover:text-emerald-400 transition">Home</a>
                    <a href="{{ route('about') }}" class="text-slate-300 hover:text-emerald-400 transition">About Us</a>
                    <a href="{{ route('contact') }}" class="text-emerald-400 font-extrabold underline underline-offset-8 decoration-emerald-500 decoration-2">Contact Us</a>
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

        <!-- Hero Banner -->
        <section class="relative py-16 lg:py-24 hero-bg-overlay px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-5">
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-slate-950/90 border-2 border-emerald-500/40 text-emerald-400 text-xs sm:text-sm font-black shadow-xl">
                    <span>GET IN TOUCH • KARNAL, HARYANA</span>
                </div>
                <h1 class="text-4xl sm:text-6xl font-black text-white">Contact <span class="gradient-text-emerald">Ashok Kumar Bans Store</span></h1>
                <p class="text-slate-200 text-base sm:text-xl font-semibold leading-relaxed">
                    Have questions about raw bamboo pricing (15ft, 20ft, 25ft), scaffolding Ghodi/Chaali, or contractor bulk rates? Reach out to us directly!
                </p>
            </div>
        </section>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <!-- Contact Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Store Contact Details Sidebar -->
                <div class="space-y-6">
                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400 flex items-center justify-center text-3xl font-bold">
                            📍
                        </div>
                        <h3 class="text-xl font-black text-white">Store Address</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">
                            <strong>Ashok Kumar Bans Store</strong><br>
                            Karnal, Haryana - 132001<br>
                            India
                        </p>
                    </div>

                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border-2 border-amber-500 text-amber-400 flex items-center justify-center text-3xl font-bold">
                            📞
                        </div>
                        <h3 class="text-xl font-black text-white">Phone & Inquiries</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">
                            <strong>Owner:</strong> Ashok Kumar<br>
                            <strong>Wholesale & Bulk Orders:</strong> Open Daily
                        </p>
                    </div>

                    <div class="glow-card p-7 rounded-3xl space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-sky-500/20 border-2 border-sky-400 text-sky-300 flex items-center justify-center text-3xl font-bold">
                            ⏰
                        </div>
                        <h3 class="text-xl font-black text-white">Business Hours</h3>
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed">
                            Monday - Saturday: 8:00 AM - 8:00 PM<br>
                            Sunday: Open for Bulk Wholesale Orders
                        </p>
                    </div>
                </div>

                <!-- Contact Form Card -->
                <div class="glow-card p-8 sm:p-10 rounded-3xl lg:col-span-2 space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white">Send Us a Message</h2>
                        <p class="text-slate-300 text-sm font-semibold mt-1">Fill out the form below and our store team in Karnal will get back to you promptly.</p>
                    </div>

                    @if(session('success'))
                        <div class="p-4 rounded-xl bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400 font-black text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-black text-white mb-2">Your Full Name</label>
                                <input type="text" name="name" required placeholder="e.g. Rajesh Sharma" class="w-full px-4 py-3.5 rounded-xl form-input-glass text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-black text-white mb-2">Phone Number</label>
                                <input type="tel" name="phone" required placeholder="e.g. 9876543210" class="w-full px-4 py-3.5 rounded-xl form-input-glass text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-black text-white mb-2">Email Address (Optional)</label>
                                <input type="email" name="email" placeholder="e.g. rajesh@example.com" class="w-full px-4 py-3.5 rounded-xl form-input-glass text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-black text-white mb-2">Inquiry Category</label>
                                <select name="inquiry_type" class="w-full px-4 py-3.5 rounded-xl form-input-glass text-sm">
                                    <option value="Raw Bamboo (15ft, 20ft, 25ft)">Raw Bamboo Purchase (15ft, 20ft, 25ft)</option>
                                    <option value="Construction Ghodi (Scaffolding)">Construction Ghodi (Scaffolding Trestles)</option>
                                    <option value="Bamboo Chaali (Platforms)">Bamboo Chaali (Work Platforms)</option>
                                    <option value="Bamboo Siddhi (Ladders)">Bamboo Siddhi (Construction Ladders)</option>
                                    <option value="Wholesale Bulk Quote">Wholesale Contractor Bulk Rates</option>
                                    <option value="General Query">General Business Query</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-white mb-2">Message / Requirement Details</label>
                            <textarea name="message" rows="4" required placeholder="Describe your quantity requirement or query..." class="w-full px-4 py-3.5 rounded-xl form-input-glass text-sm"></textarea>
                        </div>

                        <div>
                            <button type="submit" class="w-full py-4 rounded-xl btn-emerald-glow text-base font-black shadow-2xl transition">
                                Submit Inquiry to Ashok Kumar Bans Store
                            </button>
                        </div>
                    </form>
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
