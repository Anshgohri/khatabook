@props([
    'sidebar' => false,
    'textColor' => '#FFFFFF',
    'class' => '',
    'height' => '48px',
    'href' => null,
])

@php
    $linkHref = $href ?? (auth()->check() ? route('dashboard') : '/');
@endphp

<a href="{{ $linkHref }}" {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0 min-w-0 group ' . $class]) }} style="display: inline-flex; align-items: center;" aria-label="SB Baans Store">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 610 120" style="height: {{ $height }}; width: auto; max-width: 100%; display: block;">
      <!-- Bamboo "SB" Monogram (Taller & Bold) -->
      <g stroke="#8FA876" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <!-- Bamboo Stalk (Vertical spine of B) -->
        <line x1="72" y1="14" x2="72" y2="56" />
        <line x1="72" y1="64" x2="72" y2="106" />
        
        <!-- Bamboo Joint Node -->
        <path d="M 64 60 Q 72 56 80 60" stroke-width="6" />

        <!-- Stylized 'S' looping into the stalk -->
        <path d="M 62 30 C 46 14 22 16 22 36 C 22 52 68 56 68 76 C 68 96 42 104 26 88" />

        <!-- 'B' Loops -->
        <path d="M 72 18 C 104 18 108 55 74 57" />
        <path d="M 72 61 C 110 63 108 102 72 102" />

        <!-- Bamboo Leaves -->
        <path d="M 66 26 C 48 18 38 8 38 8 C 38 8 52 24 66 28 Z" fill="#8FA876" />
        <path d="M 78 24 C 94 18 104 8 104 8 C 104 8 92 22 78 26 Z" fill="#8FA876" />
      </g>

      <!-- Typography: BAANS STORE (Single text, bold, taller, tight word spacing) -->
      <text 
        x="125" 
        y="78" 
        font-family="'Montserrat', 'Arial Black', sans-serif" 
        font-size="62" 
        font-weight="900" 
        fill="{{ $textColor }}" 
        letter-spacing="-0.5">BAANS<tspan dx="10">STORE</tspan></text>
    </svg>
</a>