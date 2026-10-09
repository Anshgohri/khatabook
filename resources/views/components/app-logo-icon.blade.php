@props([
    'class' => 'h-10 w-10',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center shrink-0']) }}>
  <svg class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" style="max-height: 100%; width: auto;">
    <g stroke="#8FA876" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round">
      <line x1="72" y1="14" x2="72" y2="56" />
      <line x1="72" y1="64" x2="72" y2="106" />
      <path d="M 64 60 Q 72 56 80 60" stroke-width="6" />
      <path d="M 62 30 C 46 14 22 16 22 36 C 22 52 68 56 68 76 C 68 96 42 104 26 88" />
      <path d="M 72 18 C 104 18 108 55 74 57" />
      <path d="M 72 61 C 110 63 108 102 72 102" />
      <path d="M 66 26 C 48 18 38 8 38 8 C 38 8 52 24 66 28 Z" fill="#8FA876" />
      <path d="M 78 24 C 94 18 104 8 104 8 C 104 8 92 22 78 26 Z" fill="#8FA876" />
    </g>
  </svg>
</div>