{{-- Google reCAPTCHA v2 checkbox. Renders nothing when reCAPTCHA is disabled. --}}
@if (\App\Rules\Recaptcha::enabled())
    @once
        <script src="https://www.google.com/recaptcha/api.js?hl=bn" async defer></script>
    @endonce
    <div {{ $attributes->merge(['class' => 'flex flex-col items-start']) }}>
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        <x-input-error for="g-recaptcha-response" />
    </div>
@endif
