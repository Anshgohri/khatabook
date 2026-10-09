@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center space-y-2">
    <div class="mx-auto mb-2 flex justify-center">
        <x-app-logo href="/" textColor="#1e293b" class="h-12 w-auto" />
    </div>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">{{ $title }}</h1>
    @if ($description)
        <p class="text-xs sm:text-sm font-medium text-slate-500 leading-relaxed">{{ $description }}</p>
    @endif
</div>
