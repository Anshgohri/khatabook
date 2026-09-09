@props(['active' => 'home'])

<header x-data="{ mobileMenuOpen: false }" class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 sm:py-6 relative z-40">
    <div class="flex items-center justify-between gap-2 sm:gap-4">
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-4 group min-w-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 p-2 sm:p-3 rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-600 to-amber-500 flex items-center justify-center shadow-xl shadow-emerald-950/80 text-slate-950 font-black text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition duration-300">
                🎋
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-2xl lg:text-3xl font-black tracking-tight text-white flex items-center gap-1 truncate">
                    Ashok Kumar <span class="gradient-text-emerald">Bans Store</span>
                </span>
                <span class="block text-[10px] sm:text-xs gradient-text-amber font-bold tracking-wide truncate">
                    Ashok Kumar • Karnal, Haryana
                </span>
            </div>
        </a>

        <!-- Desktop Nav Links -->
        <nav class="hidden lg:flex items-center gap-6 text-sm font-extrabold">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-emerald-400 underline underline-offset-8 decoration-emerald-500 decoration-2' : 'text-slate-300 hover:text-emerald-400' }} transition">Home</a>
            <a href="{{ route('home') }}#products" class="text-slate-300 hover:text-emerald-400 transition">Products</a>
            <a href="{{ route('about') }}" class="{{ $active === 'about' ? 'text-emerald-400 underline underline-offset-8 decoration-emerald-500 decoration-2' : 'text-slate-300 hover:text-emerald-400' }} transition">About Us</a>
            <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? 'text-emerald-400 underline underline-offset-8 decoration-emerald-500 decoration-2' : 'text-slate-300 hover:text-emerald-400' }} transition">Contact Us</a>
        </nav>

        <!-- Right Header Action Group (Log in / Dashboard + Mobile Hamburger) -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl btn-emerald-glow text-xs sm:text-sm font-extrabold flex items-center gap-1.5">
                        <span>Dashboard</span>
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 hidden sm:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl btn-glass-secondary text-xs sm:text-sm font-extrabold">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden sm:inline-block px-5 py-2.5 rounded-xl btn-emerald-glow text-sm font-extrabold">Register</a>
                    @endif
                @endauth
            @endif

            <!-- Mobile Hamburger Button -->
            <button onclick="document.getElementById('mobile-drawer').classList.toggle('hidden')" 
                    @click="mobileMenuOpen = !mobileMenuOpen" 
                    type="button" 
                    class="lg:hidden p-2 sm:p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 hover:text-emerald-400 focus:outline-none" 
                    aria-label="Toggle Navigation">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Dropdown -->
    <div id="mobile-drawer" 
         x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.away="mobileMenuOpen = false"
         class="hidden lg:hidden mt-3 p-5 sm:p-6 rounded-2xl bg-slate-950/95 border-2 border-emerald-500/40 shadow-2xl backdrop-blur-xl space-y-4">
        <nav class="flex flex-col gap-2.5 text-sm sm:text-base font-extrabold">
            <a href="{{ route('home') }}" class="px-4 py-2.5 rounded-xl transition {{ $active === 'home' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'text-slate-200 hover:bg-slate-900' }}">Home</a>
            <a href="{{ route('home') }}#products" onclick="document.getElementById('mobile-drawer').classList.add('hidden')" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-slate-200 hover:bg-slate-900 transition">Products (Baans, Ghodi, Chaali, Siddhi)</a>
            <a href="{{ route('about') }}" class="px-4 py-2.5 rounded-xl transition {{ $active === 'about' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'text-slate-200 hover:bg-slate-900' }}">About Us</a>
            <a href="{{ route('contact') }}" class="px-4 py-2.5 rounded-xl transition {{ $active === 'contact' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'text-slate-200 hover:bg-slate-900' }}">Contact Us</a>
        </nav>

        <div class="pt-3 border-t border-slate-800 flex flex-col gap-2.5">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full py-2.5 rounded-xl btn-emerald-glow text-center font-extrabold text-sm flex items-center justify-center gap-2">
                        <span>Go to Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full py-2.5 rounded-xl btn-glass-secondary text-center font-extrabold text-sm">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="w-full py-2.5 rounded-xl btn-emerald-glow text-center font-extrabold text-sm">Register</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</header>
