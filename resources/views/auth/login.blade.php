<x-layouts.app title="লগইন">
    <x-auth-card title="আবার স্বাগতম" subtitle="ইমেইল অথবা মোবাইল নম্বর দিয়ে লগইন করুন।" icon="log-in">
        <form method="POST" action="{{ route('login') }}" x-data="submitting" @submit="submit" class="space-y-5" novalidate>
            @csrf
            <div>
                <label for="login" class="label">ইমেইল বা মোবাইল নম্বর</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username"
                       @class(['input', 'input-error' => $errors->has('login')]) placeholder="you@example.com বা 017XXXXXXXX">
                <x-input-error for="login" />
            </div>
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="label">পাসওয়ার্ড</label>
                    <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-brand-700 hover:underline">পাসওয়ার্ড ভুলে গেছেন?</a>
                </div>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                           @class(['input pr-11', 'input-error' => $errors->has('password')])>
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 hover:text-slate-700" :aria-label="show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'">
                        <span x-show="!show"><x-lucide name="eye" /></span><span x-show="show" x-cloak><x-lucide name="eye-off" /></span>
                    </button>
                </div>
                <x-input-error for="password" />
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1" class="checkbox"> লগইন করে রাখুন
            </label>
            <x-recaptcha />
            <x-submit class="w-full" loading="লগইন হচ্ছে...">লগইন</x-submit>
        </form>
        <x-slot:footer>নতুন? <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">ডোনার হিসেবে নিবন্ধন করুন</a></x-slot:footer>
    </x-auth-card>
</x-layouts.app>
