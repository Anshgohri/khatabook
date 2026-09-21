@php
    $status = $status ?? (isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500);

    $messageOverride = isset($exception) && $exception->getMessage() ? $exception->getMessage() : null;

    $details = match ((int) $status) {
        401 => [
            'title' => __('Authentication Required'),
            'subtitle' => __('HTTP 401 Unauthorized'),
            'icon' => '🔑',
            'message' => $messageOverride ?: __('You must be logged in to access this page. Please sign in to continue.'),
        ],
        403 => [
            'title' => __('Access Forbidden'),
            'subtitle' => __('HTTP 403 Forbidden'),
            'icon' => '🔒',
            'message' => $messageOverride ?: __('You do not have permission to view or manage this resource. Please contact your store manager.'),
        ],
        404 => [
            'title' => __('Page Not Found'),
            'subtitle' => __('HTTP 404 Not Found'),
            'icon' => '🔍',
            'message' => $messageOverride ?: __('The page or resource you are looking for could not be found or has been moved to a new web address.'),
        ],
        419 => [
            'title' => __('Page Session Expired'),
            'subtitle' => __('HTTP 419 Page Expired'),
            'icon' => '⏱️',
            'message' => $messageOverride ?: __('Your security session has expired due to inactivity. Please refresh the page and try again.'),
        ],
        429 => [
            'title' => __('Too Many Requests'),
            'subtitle' => __('HTTP 429 Rate Limit Exceeded'),
            'icon' => '⚡',
            'message' => $messageOverride ?: __('You have sent too many requests in a short amount of time. Please wait a few moments before trying again.'),
        ],
        503 => [
            'title' => __('Service Unavailable'),
            'subtitle' => __('HTTP 503 Maintenance'),
            'icon' => '🚧',
            'message' => $messageOverride ?: __('The system is currently undergoing scheduled maintenance or system updates. Please check back shortly.'),
        ],
        default => [
            'title' => __('Internal Server Error'),
            'subtitle' => __('HTTP ' . $status . ' Server Error'),
            'icon' => '🛠️',
            'message' => $messageOverride ?: __('An unexpected error occurred while processing your request. Our system log has captured the details.'),
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} - {{ $details['title'] }} | Ashok Kumar Baans Store, Karnal</title>

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
    @include('partials.public-header', ['active' => 'error'])

    <!-- Hero / Error Section -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 flex items-center justify-center">
        <div class="w-full text-center space-y-8">
            
            <!-- Status Code Badge & Emoji Icon -->
            <div class="relative inline-block">
                <span class="text-8xl sm:text-[11rem] font-black tracking-tighter bg-gradient-to-r from-emerald-600 via-teal-600 to-slate-900 bg-clip-text text-transparent select-none drop-shadow-xs leading-none">
                    {{ $status }}
                </span>

                <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 bg-white border border-slate-200 shadow-xl rounded-2xl p-3 sm:p-4 text-2xl sm:text-4xl animate-bounce">
                    {{ $details['icon'] }}
                </div>
            </div>

            <!-- Error Card -->
            <div class="bg-white border border-slate-200 p-8 sm:p-12 rounded-3xl shadow-xl space-y-5 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    {{ $details['subtitle'] }}
                </span>

                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $details['title'] }}
                </h1>

                <p class="text-slate-600 font-medium text-sm sm:text-base leading-relaxed max-w-lg mx-auto">
                    {{ $details['message'] }}
                </p>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('home') }}'" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 font-bold transition flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                        <flux:icon icon="arrow-left" class="w-4 h-4 text-slate-500" />
                        <span>{{ __('Go Back') }}</span>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-md flex items-center justify-center gap-2 text-xs sm:text-sm">
                            <flux:icon icon="home" class="w-4 h-4" />
                            <span>{{ __('Return to Dashboard') }}</span>
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-md flex items-center justify-center gap-2 text-xs sm:text-sm">
                            <flux:icon icon="home" class="w-4 h-4" />
                            <span>{{ __('Back to Store Home') }}</span>
                        </a>
                    @endauth

                    <a href="{{ route('contact') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black shadow-md transition flex items-center justify-center gap-2 text-xs sm:text-sm">
                        <flux:icon icon="question-mark-circle" class="w-4 h-4" />
                        <span>{{ __('Contact Support') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Public Footer -->
    <footer class="w-full border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-600 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="font-semibold">© {{ date('Y') }} Ashok Kumar Baans Store, Karnal. All rights reserved.</span>
            <div class="flex items-center gap-6 font-bold text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">{{ __('Home') }}</a>
                <a href="{{ route('catalog.index') }}" class="hover:text-emerald-600 transition">{{ __('Products Catalog') }}</a>
                <a href="{{ route('about') }}" class="hover:text-emerald-600 transition">{{ __('About Us') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-emerald-600 transition">{{ __('Contact Us') }}</a>
            </div>
            <span class="text-emerald-600 font-extrabold">Ashok Kumar • Karnal, Haryana</span>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
