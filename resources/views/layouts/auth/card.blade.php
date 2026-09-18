<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')

        <style>
            [x-cloak] {
                display: none !important;
            }
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
            }
            .auth-card {
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01) !important;
            }
            .auth-card label, 
            .auth-card [data-flux-label],
            .auth-card [data-slot="label"] {
                color: #334155 !important;
                font-weight: 700 !important;
                font-size: 0.8125rem !important;
            }
            .auth-card input[type="text"],
            .auth-card input[type="email"],
            .auth-card input[type="password"] {
                background-color: #f8fafc !important;
                color: #0f172a !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0.75rem !important;
            }
            .auth-card input::placeholder {
                color: #94a3b8 !important;
            }
            .auth-card input:focus {
                background-color: #ffffff !important;
                border-color: #10b981 !important;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
            }
            .auth-card p, 
            .auth-card span,
            .auth-card [data-flux-subheading],
            .auth-card [data-flux-description] {
                color: #64748b !important;
            }
            .auth-card h1, .auth-card h2, .auth-card h3,
            .auth-card [data-flux-heading] {
                color: #0f172a !important;
                font-weight: 900 !important;
            }
        </style>
    </head>
    <body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">
        
        <!-- Header -->
        @include('partials.public-header', ['active' => 'login'])

        <!-- Form Card Section -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 flex items-center justify-center">
            <div class="w-full max-w-md auth-card p-6 sm:p-10 rounded-3xl space-y-6">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-600 mt-auto">
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

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
