{{-- Session flash messages as accessible, auto-dismissing toasts. --}}
@php
    $styles = [
        'success' => ['circle-check', 'border-emerald-200 bg-emerald-50 text-emerald-900', 'text-emerald-600'],
        'error' => ['circle-x', 'border-brand-200 bg-brand-50 text-brand-900', 'text-brand-600'],
        'warning' => ['triangle-alert', 'border-amber-200 bg-amber-50 text-amber-900', 'text-amber-600'],
        'info' => ['info', 'border-sky-200 bg-sky-50 text-sky-900', 'text-sky-600'],
    ];
@endphp
<div class="pointer-events-none fixed inset-x-0 top-20 z-50 flex flex-col items-center gap-2 px-4" aria-live="polite">
    @foreach ($styles as $key => [$icon, $box, $iconColor])
        @if (session($key))
            <div x-data="toast({{ $key === 'error' ? 10000 : 6000 }})" x-show="show" x-transition.opacity.duration.300ms
                 role="{{ $key === 'error' ? 'alert' : 'status' }}"
                 class="pointer-events-auto flex w-full max-w-lg items-start gap-3 rounded-2xl border px-4 py-3 text-sm shadow-lift {{ $box }}">
                <x-lucide :name="$icon" class="mt-0.5 size-5 {{ $iconColor }}" />
                <p class="flex-1 font-medium">{{ session($key) }}</p>
                <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><x-lucide name="x" /></button>
            </div>
        @endif
    @endforeach
</div>
