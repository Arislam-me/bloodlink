@props(['variant' => 'btn-primary', 'loading' => 'অপেক্ষা করুন...'])
{{-- Submit button with loading state. Parent <form> must have x-data="submitting" @submit="submit". --}}
<button type="submit" {{ $attributes->merge(['class' => $variant]) }} :disabled="busy">
    <span x-show="!busy" class="inline-flex items-center gap-2">{{ $slot }}</span>
    <span x-show="busy" x-cloak class="inline-flex items-center gap-2"><x-lucide name="loader-circle" class="size-4 animate-spin" /> {{ $loading }}</span>
</button>
