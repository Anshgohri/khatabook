@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center space-y-2">
    <div class="mx-auto mb-4">
        <div class="max-w-xs sm:max-w-sm overflow-hidden rounded-2xl shadow-2xl shadow-emerald-950/60 bg-slate-950/90 border-2 border-emerald-500/40 p-2.5 flex items-center justify-center mx-auto backdrop-blur-xl group hover:border-emerald-400 transition duration-300">
            <img src="{{ asset('images/ak-logo.png') }}" alt="AK - Ashok Kumar Baans Store Logo" class="h-20 sm:h-28 w-auto object-contain filter drop-shadow-lg" />
        </div>
    </div>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white drop-shadow-sm">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm font-medium text-slate-300 leading-relaxed">{{ $description }}</p>
    @endif
</div>

