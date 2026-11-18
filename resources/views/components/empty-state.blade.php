@props(['icon' => 'inbox', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-14 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-500"><x-lucide :name="$icon" class="size-7" /></span>
    <h3 class="mt-4 text-base font-bold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-slate-600">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
