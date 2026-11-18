{{-- reCAPTCHA v2 widget for the Filament admin login. Writes the token into the Livewire form state. --}}
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore
         x-data="{
             widget: null,
             init() {
                 const render = () => {
                     if (! window.grecaptcha || ! window.grecaptcha.render) { return setTimeout(render, 200); }
                     this.widget = window.grecaptcha.render(this.$refs.box, {
                         sitekey: @js(config('services.recaptcha.site_key')),
                         callback: (token) => $wire.set('{{ $getStatePath() }}', token, false),
                         'expired-callback': () => $wire.set('{{ $getStatePath() }}', null, false),
                     });
                 };
                 render();
             },
         }"
         x-on:recaptcha-reset.window="if (widget !== null) { window.grecaptcha.reset(widget); $wire.set('{{ $getStatePath() }}', null, false) }">
        <div x-ref="box"></div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
</x-dynamic-component>
