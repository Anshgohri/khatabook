<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Thank You - Ashok Kumar Baans Store, Karnal</title>

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
    @include('partials.public-header', ['active' => 'thank-you'])

    <!-- Main Content -->
    <main class="flex-1 max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-16 flex items-center justify-center">
        <div class="bg-white p-8 sm:p-14 rounded-3xl border border-slate-200 shadow-sm text-center space-y-6 w-full">
            <!-- Checkmark Icon -->
            <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mx-auto font-black shadow-xs">
                ✓
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                <span>Inquiry Submitted Successfully!</span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-black text-slate-900">
                Thank You for Reaching Out!
            </h1>

            <p class="text-slate-600 text-sm sm:text-base font-medium leading-relaxed max-w-xl mx-auto">
                We have received your inquiry at <strong>Ashok Kumar Baans Store, Karnal</strong>. Our store team will review your requirement and reach out to you on your contact number shortly.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm transition inline-flex items-center gap-2">
                    <span>Return to Home</span>
                </a>
                <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-black shadow-sm transition">
                    Browse All Products Catalog
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