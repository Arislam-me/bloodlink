@props(['name'])
{{-- Lucide icon placeholder; replaced with inline SVG by resources/js/icons.js --}}
<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => 'size-4 inline-block']) }}></i>
