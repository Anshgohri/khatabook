<header class="w-full border-b border-slate-200 bg-white/80 backdrop-blur-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-slate-800">
                <x-app-logo-icon class="w-8 h-8 text-emerald-600" />
                <span class="hidden sm:block">Khatabook</span>
            </a>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors {{ ($active ?? '') === 'home' ? 'text-emerald-600' : '' }}">Home</a>
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors {{ ($active ?? '') === 'login' ? 'text-emerald-600' : '' }}">Log in</a>
        </div>
    </div>
</header>
