@props(['user', 'masked' => false])
{{-- Photo if available (and not masked), otherwise initials. --}}
@if (! $masked && $user->profilePhotoUrl())
    <img src="{{ $user->profilePhotoUrl() }}" alt="" {{ $attributes->merge(['class' => 'size-10 rounded-full object-cover ring-2 ring-white']) }}>
@else
    <span {{ $attributes->merge(['class' => 'size-10 grid place-items-center rounded-full bg-gradient-to-br from-brand-100 to-brand-200 font-bold text-brand-800 ring-2 ring-white']) }} aria-hidden="true">
        {{ $masked ? Str::upper(Str::substr($user->name, 0, 1)) : $user->initials() }}
    </span>
@endif
