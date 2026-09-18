@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center space-y-2">
    <div class="mx-auto mb-2">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-900 border-2 border-emerald-500/40 p-1.5 shadow-md flex items-center justify-center mx-auto transition duration-300">
            <img src="{{ asset('images/ak-emblem.png') }}" alt="AK Logo" class="h-full w-full object-contain" />
        </div>
    </div>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">{{ $title }}</h1>
    @if ($description)
        <p class="text-xs sm:text-sm font-medium text-slate-500 leading-relaxed">{{ $description }}</p>
    @endif
</div>
