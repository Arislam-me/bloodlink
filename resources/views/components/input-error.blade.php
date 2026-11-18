@props(['for', 'bag' => 'default'])
@error($for, $bag)
    <p {{ $attributes->merge(['class' => 'mt-1.5 flex items-center gap-1 text-xs font-medium text-brand-700']) }} role="alert">
        <x-lucide name="circle-x" class="size-3.5" /> {{ $message }}
    </p>
@enderror
