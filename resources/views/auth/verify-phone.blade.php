<x-layouts.app title="মোবাইল যাচাই">
    <x-auth-card title="মোবাইল নম্বর যাচাই করুন" subtitle="{{ $user->maskedPhone() }} নম্বরে পাঠানো ৬ সংখ্যার কোডটি লিখুন।" icon="phone">
        <form method="POST" action="{{ route('phone.verify') }}" x-data="submitting" @submit="submit" class="space-y-5">
            @csrf
            <div>
                <label for="code" class="label">যাচাই কোড</label>
                <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="{{ config('bloodlink.otp.length') }}" autocomplete="one-time-code" required autofocus
                       @class(['input text-center text-2xl font-bold tracking-[0.5em]', 'input-error' => $errors->has('code')]) placeholder="••••••">
                <x-input-error for="code" />
                <p class="hint">কোডটি {{ config('bloodlink.otp.ttl_minutes') }} মিনিটের মধ্যে ব্যবহার করুন। কাউকে এই কোড জানাবেন না।</p>
            </div>
            <x-submit class="w-full" loading="যাচাই হচ্ছে...">যাচাই করুন</x-submit>
        </form>
        <div class="mt-6 flex flex-col items-center gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
            <form method="POST" action="{{ route('phone.verify.send') }}">
                @csrf
                <button class="btn-ghost btn-sm"><x-lucide name="refresh-cw" class="size-3.5" /> নতুন কোড পাঠান</button>
            </form>
            <a href="{{ route('profile.edit') }}" class="btn-ghost btn-sm">নম্বর বদলান</a>
        </div>
        @if (config('bloodlink.sms.driver') === 'log' && ! app()->isProduction())
            <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">
                <strong>ডেভেলপমেন্ট মোড:</strong> SMS আসলে পাঠানো হয় না। কোড দেখতে চালান: <code>php artisan bloodlink:codes {{ $user->phone }}</code>
            </p>
        @endif
    </x-auth-card>
</x-layouts.app>
