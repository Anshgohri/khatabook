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
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', [
            'title' => $status . ' - ' . $details['title']
        ])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-emerald-500 selection:text-white relative overflow-x-hidden">
        <!-- Ambient Glowing Background Elements -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/3 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Header Bar -->
        <header class="w-full border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <x-app-logo class="h-9 w-auto text-emerald-400" />
                </a>

                <nav class="flex items-center gap-4 text-sm font-semibold text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-emerald-400 transition hidden sm:inline-block">
                        {{ __('Home') }}
                    </a>
                    <a href="{{ route('catalog.index') }}" class="hover:text-emerald-400 transition hidden sm:inline-block">
                        {{ __('Catalog') }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition shadow-lg shadow-emerald-900/30 flex items-center gap-1.5 text-xs">
                            <flux:icon icon="home" class="w-4 h-4" />
                            <span>{{ __('Dashboard') }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold transition flex items-center gap-1.5 text-xs">
                            <flux:icon icon="arrow-right-end-on-rectangle" class="w-4 h-4 text-emerald-400" />
                            <span>{{ __('Log In') }}</span>
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 flex items-center justify-center">
            <div class="w-full text-center space-y-8">
                <!-- Large Status Code & Icon -->
                <div class="relative inline-block">
                    <span class="text-8xl sm:text-[11rem] font-black tracking-tighter bg-gradient-to-br from-emerald-400 via-teal-300 to-indigo-500 bg-clip-text text-transparent select-none drop-shadow-2xl leading-none">
                        {{ $status }}
                    </span>

                    <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 bg-slate-900/90 border border-slate-700 shadow-xl rounded-2xl p-3 sm:p-4 text-2xl sm:text-4xl animate-bounce">
                        {{ $details['icon'] }}
                    </div>
                </div>

                <!-- Error Messaging Card -->
                <div class="bg-slate-900/80 border border-slate-800/90 backdrop-blur-xl p-8 sm:p-12 rounded-3xl shadow-2xl space-y-4 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        {{ $details['subtitle'] }}
                    </span>

                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        {{ $details['title'] }}
                    </h1>

                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-lg mx-auto">
                        {{ $details['message'] }}
                    </p>

                    <!-- Action Buttons -->
                    <div class="pt-6 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                        <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('home') }}'" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold transition flex items-center justify-center gap-2 text-sm">
                            <flux:icon icon="arrow-left" class="w-4 h-4 text-slate-400" />
                            <span>{{ __('Go Back') }}</span>
                        </button>

                        @auth
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition shadow-lg shadow-emerald-900/40 flex items-center justify-center gap-2 text-sm">
                                <flux:icon icon="home" class="w-4 h-4" />
                                <span>{{ __('Return to Dashboard') }}</span>
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition shadow-lg shadow-emerald-900/40 flex items-center justify-center gap-2 text-sm">
                                <flux:icon icon="home" class="w-4 h-4" />
                                <span>{{ __('Back to Store Home') }}</span>
                            </a>
                        @endauth

                        <a href="{{ route('contact') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-slate-200 font-semibold transition flex items-center justify-center gap-2 text-sm">
                            <flux:icon icon="question-mark-circle" class="w-4 h-4" />
                            <span>{{ __('Contact Support') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-800/80 bg-slate-950 py-6 text-center text-xs text-slate-500 mt-auto">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span>© {{ date('Y') }} Ashok Kumar Baans Store, Karnal. All rights reserved.</span>
                <div class="flex items-center gap-6 font-semibold text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-emerald-400 transition">{{ __('Home') }}</a>
                    <a href="{{ route('catalog.index') }}" class="hover:text-emerald-400 transition">{{ __('Products') }}</a>
                    <a href="{{ route('about') }}" class="hover:text-emerald-400 transition">{{ __('About') }}</a>
                    <a href="{{ route('contact') }}" class="hover:text-emerald-400 transition">{{ __('Contact') }}</a>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
