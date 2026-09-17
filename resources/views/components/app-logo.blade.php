@props([
    'sidebar' => false,
])

<a href="{{ route('dashboard') }}" {{ $attributes->merge(['class' => 'flex items-center group shrink-0 min-w-0 py-1.5']) }} wire:navigate aria-label="Ashok Kumar Baans Store Dashboard">
    <!-- Impressive 3D Store Name Typography -->
    <div class="flex flex-col min-w-0">
        <span class="text-lg sm:text-2xl font-black tracking-wider uppercase text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-400 filter drop-shadow-[0_2px_0px_rgba(4,120,87,0.7)] group-hover:from-emerald-400 group-hover:to-amber-300 transition-all leading-tight truncate">
            ASHOK KUMAR
        </span>
        <div class="flex items-center gap-2">
            <span class="text-xs sm:text-sm font-black tracking-widest uppercase text-slate-800 dark:text-white filter drop-shadow-[0_1.5px_0px_rgba(16,185,129,0.5)] truncate">
                BAANS STORE
            </span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-black tracking-widest text-emerald-300 bg-emerald-950 border border-emerald-500/50 shadow-inner hidden sm:inline-block">
                KARNAL
            </span>
        </div>
    </div>
</a>
