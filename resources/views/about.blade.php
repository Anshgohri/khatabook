<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Us - Ashok Kumar Baans Store, Karnal</title>
    <meta name="description" content="Learn about Ashok Kumar Baans Store in Karnal, Haryana. Direct supplier of raw bamboo poles, scaffolding Ghodi trestles, Chaali platforms, and Siddhi ladders.">

    <link rel="icon" type="image/png" href="/favicon.png?v=1">
    <link rel="icon" href="/favicon.ico?v=1" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Header Navigation -->
    @include('partials.public-header', ['active' => 'about'])

    <!-- Hero Title Banner -->
    <section class="bg-white border-b border-slate-200/80 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center space-y-4">
            <span class="px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider">
                TRUSTED BAMBOO MERCHANTS • KARNAL, HARYANA
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900">
                About <span class="text-emerald-600">Ashok Kumar Baans Store</span>
            </h1>
            <p class="text-slate-600 text-base sm:text-lg font-medium leading-relaxed">
                Providing premium raw bamboo poles and custom construction scaffolding equipment for building contractors and retail buyers across Haryana for over 40 years.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Business Story Column -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xs lg:col-span-2 space-y-6">
                <h2 class="text-2xl font-black text-slate-900">Our Business Story</h2>
                <p class="text-slate-700 text-sm sm:text-base leading-relaxed font-medium">
                    <strong>Ashok Kumar Baans Store</strong> is a premier bamboo merchant located in <strong>Karnal, Haryana</strong>, operated under <strong>Ashok Kumar</strong>. We specialize in sourcing top-grade raw bamboos in standard 15 feet, 20 feet, and 25 feet lengths directly for construction projects.
                </p>
                <p class="text-slate-700 text-sm sm:text-base leading-relaxed font-medium">
                    In addition to raw bamboo supply, we run a dedicated processing unit where skilled craftsmen manufacture construction essential scaffolding equipment:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-3xl mb-2">🪜</div>
                        <h4 class="font-black text-slate-900 text-sm">Scaffolding Ghodi</h4>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Heavy-duty bamboo trestles for sturdy building support.</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-3xl mb-2">🧱</div>
                        <h4 class="font-black text-slate-900 text-sm">Bamboo Chaali</h4>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Woven work platforms for safe high-rise construction.</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-3xl mb-2">🧗</div>
                        <h4 class="font-black text-slate-900 text-sm">Bamboo Siddhi</h4>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Single and double ladders crafted in all reach heights.</p>
                    </div>
                </div>
            </div>

            <!-- Business Highlights Sidebar -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-2">
                    <div class="text-3xl">📍</div>
                    <h3 class="text-base font-black text-slate-900">Direct Store Yard</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        House No 2755, Opposite Gaushala Road, Janak Puri, Karnal, Haryana - 132001
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-2">
                    <div class="text-3xl">💼</div>
                    <h3 class="text-base font-black text-slate-900">Wholesale & Retail</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Bulk contractor rates and flexible retail billing for market buyers.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-2">
                    <div class="text-3xl">⚡</div>
                    <h3 class="text-base font-black text-slate-900">Digital Ledger</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        100% transparent digital system tracking stock levels and customer credit.
                    </p>
                </div>
            </div>
        </div>

        <!-- CTA Box -->
        <div class="bg-gradient-to-r from-emerald-800 to-slate-900 text-white p-8 sm:p-12 rounded-3xl text-center space-y-4 shadow-lg">
            <h2 class="text-2xl sm:text-3xl font-black text-white">Need Raw Bamboo or Scaffolding Equipment?</h2>
            <p class="text-emerald-100 text-xs sm:text-sm font-medium max-w-xl mx-auto">Get in touch with us today for bulk wholesale quotes, custom Ghodi/Chaali sizes, or store visits in Karnal.</p>
            <div class="pt-2">
                <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs sm:text-sm font-black inline-block shadow-md transition">
                    Contact Ashok Kumar Baans Store
                </a>
            </div>
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