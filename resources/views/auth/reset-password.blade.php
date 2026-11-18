<x-layouts.app title="নতুন পাসওয়ার্ড">
    <x-auth-card title="নতুন পাসওয়ার্ড দিন" icon="key-round">
        <form method="POST" action="{{ route('password.store') }}" x-data="submitting" @submit="submit" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="label">ইমেইল</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" @class(['input', 'input-error' => $errors->has('email')])>
                <x-input-error for="email" />
            </div>
            <div>
                <label for="password" class="label">নতুন পাসওয়ার্ড</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" @class(['input', 'input-error' => $errors->has('password')])>
                <x-input-error for="password" />
            </div>
            <div>
                <label for="password_confirmation" class="label">নতুন পাসওয়ার্ড আবার লিখুন</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
            </div>
            <x-submit class="w-full" loading="সংরক্ষণ হচ্ছে...">পাসওয়ার্ড সংরক্ষণ করুন</x-submit>
        </form>
    </x-auth-card>
</x-layouts.app>
