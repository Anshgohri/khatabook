@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Ashok Kumar Bans Store" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-amber-500 text-slate-950 shadow-md">
            <x-app-logo-icon />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Ashok Kumar Bans Store" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-amber-500 text-slate-950 shadow-md">
            <x-app-logo-icon />
        </x-slot>
    </flux:brand>
@endif
