@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center space-y-2">
    <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mx-auto mb-1">
        <span>🎋</span> ASHOK KUMAR BANS STORE
    </div>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white drop-shadow-sm">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm font-medium text-slate-300 leading-relaxed">{{ $description }}</p>
    @endif
</div>

