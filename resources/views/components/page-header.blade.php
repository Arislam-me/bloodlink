@props(['title', 'subtitle' => null, 'icon' => null])
<div class="border-b border-slate-200/70 bg-white">
    <div class="container-page flex flex-col gap-4 py-8 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-start gap-4">
            @if ($icon)
                <span class="hidden size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700 sm:grid"><x-lucide :name="$icon" class="size-6" /></span>
            @endif
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 max-w-2xl text-sm text-slate-600 sm:text-base">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if ($slot->isNotEmpty())
            <div class="flex flex-wrap gap-2">{{ $slot }}</div>
        @endif
    </div>
</div>
