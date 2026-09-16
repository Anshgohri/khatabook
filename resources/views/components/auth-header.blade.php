@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center space-y-2">
    <div class="mx-auto mb-3">
        <img src="{{ asset('images/logo.png') }}" class="h-16 w-auto rounded-xl shadow-lg shadow-emerald-900/20 mx-auto" alt="Ashok Kumar Baans Store Logo" />
    </div>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white drop-shadow-sm">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm font-medium text-slate-300 leading-relaxed">{{ $description }}</p>
    @endif
</div>

