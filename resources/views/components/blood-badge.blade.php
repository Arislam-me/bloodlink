@props(['code', 'size' => 'md'])
@php
    $sizes = ['sm' => 'size-10 text-sm', 'md' => 'size-14 text-lg', 'lg' => 'size-20 text-2xl'];
@endphp
<span {{ $attributes->merge(['class' => 'grid shrink-0 place-items-center rounded-2xl bg-brand-700 font-extrabold text-white shadow-sm '.$sizes[$size]]) }}
      aria-label="Blood group {{ $code }}">{{ $code }}</span>
