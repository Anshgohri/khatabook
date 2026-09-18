<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact Us - Ashok Kumar Baans Store, Karnal</title>
    <meta name="description" content="Contact Ashok Kumar Baans Store in Karnal, Haryana. Direct phone contacts, store address, working hours, and wholesale rate inquiry form.">

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
    @include('partials.public-header', ['active' => 'contact'])

    <!-- Hero Title Banner -->
    <section class="bg-white border-b border-slate-200/80 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center space-y-4">
            <span class="px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider">
                GET IN TOUCH • KARNAL, HARYANA
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900">
                Contact <span class="text-emerald-600">Ashok Kumar Baans Store</span>
            </h1>
            <p class="text-slate-600 text-base sm:text-lg font-medium leading-relaxed">
                Have questions about raw bamboo pricing (15ft, 20ft, 25ft), scaffolding Ghodi/Chaali, or contractor bulk rates? Reach out to us directly!
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Details -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-black">
                        📍
                    </div>
                    <h3 class="text-base font-black text-slate-900">Store Yard Address</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        <strong>Ashok Kumar Baans Store</strong><br>
                        House No 2755, Opposite Gaushala Road,<br>
                        Janak Puri, Karnal, Haryana - 132001
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-black">
                        📞
                    </div>
                    <h3 class="text-base font-black text-slate-900">Phone Contacts</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        <strong>Ashok Kumar:</strong> <a href="tel:+919254998000" class="text-emerald-700 font-bold hover:underline">+91 92549 98000</a>, <a href="tel:+919255523276" class="text-emerald-700 font-bold hover:underline">+91 92555 23276</a><br>
                        <strong>Ansh:</strong> <a href="tel:+918950304888" class="text-emerald-700 font-bold hover:underline">+91 89503 04888</a><br>
                        <strong>Email:</strong> anshgohri8950@gmail.com
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-black">
                        ⏰
                    </div>
                    <h3 class="text-base font-black text-slate-900">Working Hours</h3>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Monday – Sunday: <span class="text-slate-900 font-bold">6:00 AM – 10:00 PM</span><br>
                        Open year-round for bulk wholesale loading.
                    </p>
                </div>
            </div>

            <!-- Inquiry Form Card -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xs lg:col-span-2 space-y-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-900">Send Us a Message</h2>
                    <p class="text-xs text-slate-500 font-medium mt-1">Fill out the form below and our store team in Karnal will get back to you promptly.</p>
                </div>

                @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs text-center">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">Your Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Rajesh Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">Email Address (Optional)</label>
                            <input type="email" name="email" placeholder="e.g. rajesh@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">Inquiry Category *</label>
                            <select name="inquiry_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="Raw Bamboo (15ft, 20ft, 25ft)">Raw Bamboo Purchase (15ft, 20ft, 25ft)</option>
                                <option value="Construction Ghodi (Scaffolding)">Construction Ghodi (Scaffolding Trestles)</option>
                                <option value="Bamboo Chaali (Platforms)">Bamboo Chaali (Work Platforms)</option>
                                <option value="Bamboo Siddhi (Ladders)">Bamboo Siddhi (Construction Ladders)</option>
                                <option value="Wholesale Bulk Quote">Wholesale Contractor Bulk Rates</option>
                                <option value="General Query">General Business Query</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">Message / Requirement Details *</label>
                        <textarea name="message" rows="4" required placeholder="Describe your quantity requirement or site delivery location..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition">
                        Submit Inquiry to Ashok Kumar Baans Store
                    </button>
                </form>
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