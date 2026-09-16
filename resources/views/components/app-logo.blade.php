@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand href="{{ route('dashboard') }}" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Ashok Kumar Baans Store Logo" class="h-10 w-auto rounded-lg shadow-sm" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand href="{{ route('dashboard') }}" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Ashok Kumar Baans Store Logo" class="h-12 w-auto rounded-lg shadow-md" />
        </x-slot>
    </flux:brand>
@endif
