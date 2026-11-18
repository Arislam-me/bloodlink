@props(['title', 'subtitle' => null, 'icon' => 'droplet', 'width' => 'max-w-md'])
<div class="container-page flex justify-center py-10 sm:py-16">
    <div class="w-full {{ $width }}">
        <div class="mb-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-700 text-white shadow-sm"><x-lucide :name="$icon" class="size-7" /></span>
            <h1 class="mt-4 text-2xl font-extrabold tracking-tight">{{ $title }}</h1>
            @if ($subtitle)<p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>@endif
        </div>
        <div class="card card-body sm:p-8">{{ $slot }}</div>
        @isset($footer)<div class="mt-6 text-center text-sm text-slate-600">{{ $footer }}</div>@endisset
    </div>
</div>
