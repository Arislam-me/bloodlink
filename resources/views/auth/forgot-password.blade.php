<x-layouts.app title="পাসওয়ার্ড ভুলে গেছেন">
    <x-auth-card title="পাসওয়ার্ড নতুন করে দিন" subtitle="অ্যাকাউন্টের ইমেইল দিন, আমরা একটি নিরাপদ লিংক পাঠাব।" icon="key-round">
        <form method="POST" action="{{ route('password.email') }}" x-data="submitting" @submit="submit" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="label">ইমেইল</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" @class(['input', 'input-error' => $errors->has('email')])>
                <x-input-error for="email" />
            </div>
            <x-submit class="w-full" loading="পাঠানো হচ্ছে...">লিংক পাঠান</x-submit>
        </form>
        <x-slot:footer><a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">লগইন পেজে ফিরে যান</a></x-slot:footer>
    </x-auth-card>
</x-layouts.app>
