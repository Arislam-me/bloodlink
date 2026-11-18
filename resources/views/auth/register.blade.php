<x-layouts.app title="ডোনার হিসেবে নিবন্ধন">
    <x-auth-card title="রক্তদাতা হিসেবে নিবন্ধন করুন" subtitle="মাত্র দুই মিনিট লাগবে। খোঁজার তালিকায় আপনার নম্বর দেখানো হয় না।" icon="user-plus" width="max-w-3xl">
        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" x-data="submitting" @submit="submit" class="space-y-8">
            @csrf
            {{-- Honeypot (hidden from humans and screen readers) --}}
            <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-base font-bold"><x-lucide name="user" class="size-5 text-brand-700" /> আপনার তথ্য</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">পুরো নাম <span class="text-brand-700">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" @class(['input', 'input-error' => $errors->has('name')])>
                        <x-input-error for="name" />
                    </div>
                    <div>
                        <label for="email" class="label">ইমেইল <span class="text-brand-700">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @class(['input', 'input-error' => $errors->has('email')])>
                        <p class="hint">পাসওয়ার্ড ভুলে গেলে এই ইমেইলে লিংক পাঠানো হবে।</p>
                        <x-input-error for="email" />
                    </div>
                    <div>
                        <label for="phone" class="label">মোবাইল নম্বর <span class="text-brand-700">*</span></label>
                        <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone') }}" required autocomplete="tel" placeholder="01XXXXXXXXX" @class(['input', 'input-error' => $errors->has('phone')])>
                        <p class="hint">যাচাইয়ের জন্য এই নম্বরে SMS কোড পাঠানো হবে।</p>
                        <x-input-error for="phone" />
                    </div>
                    <div>
                        <label for="password" class="label">পাসওয়ার্ড <span class="text-brand-700">*</span></label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" @class(['input', 'input-error' => $errors->has('password')])>
                        <p class="hint">কমপক্ষে ৮ অক্ষর, অক্ষর ও সংখ্যা দুটোই থাকবে।</p>
                        <x-input-error for="password" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">পাসওয়ার্ড আবার লিখুন <span class="text-brand-700">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-base font-bold"><x-lucide name="calendar" class="size-5 text-brand-700" /> জন্মতারিখ ও ওজন</legend>
                <x-basics-fields />
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-base font-bold"><x-lucide name="droplet" class="size-5 text-brand-700" /> রক্ত ও রক্তদান</legend>
                <div>
                    <span class="label">রক্তের গ্রুপ <span class="text-brand-700">*</span></span>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-8" role="radiogroup" aria-label="রক্তের গ্রুপ">
                        @foreach ($bloodGroups as $bg)
                            <label class="cursor-pointer">
                                <input type="radio" name="blood_group_id" value="{{ $bg->id }}" class="peer sr-only" @checked(old('blood_group_id') == $bg->id) required>
                                <span class="block rounded-xl border border-slate-200 py-2.5 text-center text-sm font-bold transition peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 hover:border-brand-300">{{ $bg->code }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error for="blood_group_id" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="last_donation_date" class="label">শেষ কবে রক্ত দিয়েছেন <span class="font-normal text-slate-500">(যদি দিয়ে থাকেন)</span></label>
                        <input id="last_donation_date" name="last_donation_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('last_donation_date') }}" @class(['input', 'input-error' => $errors->has('last_donation_date')])>
                        <p class="hint">৪ মাসের মধ্যে রক্ত দিয়ে থাকলে ততদিন আপনাকে "বিশ্রামে" দেখানো হবে।</p>
                        <x-input-error for="last_donation_date" />
                    </div>
                    <div>
                        <label for="photo" class="label">প্রোফাইল ছবি <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold hover:file:bg-slate-200">
                        <p class="hint">JPG, PNG বা WebP, সর্বোচ্চ ২ MB।</p>
                        <x-input-error for="photo" />
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-base font-bold"><x-lucide name="globe" class="size-5 text-brand-700" /> সোশ্যাল মিডিয়া</legend>
                <x-social-fields />
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-base font-bold"><x-lucide name="map-pin" class="size-5 text-brand-700" /> আপনার এলাকা</legend>
                <x-location-fields :divisions="$divisions" with-area :values="[
                    'division' => old('division_id'), 'district' => old('district_id'), 'upazila' => old('upazila_id'),
                    'areaName' => old('area_name'),
                ]" />
            </fieldset>

            <fieldset class="space-y-3 rounded-2xl bg-slate-50 p-4 sm:p-5">
                <legend class="sr-only">সম্মতি</legend>
                <label class="flex items-start gap-3 text-sm">
                    <input type="checkbox" name="terms" value="1" class="checkbox mt-0.5" required @checked(old('terms'))>
                    <span>
                        আমি <a href="{{ route('terms') }}" target="_blank" class="font-semibold text-brand-700 underline">শর্তাবলি</a> ও
                        <a href="{{ route('privacy') }}" target="_blank" class="font-semibold text-brand-700 underline">গোপনীয়তা নীতি</a> পড়েছি এবং রাজি আছি।
                        আমি জানি, মোবাইল যাচাই করা সদস্যরা আমার প্রোফাইল খুলে আমার মোবাইল নম্বর দেখতে পারবেন। <span class="text-brand-700">*</span>
                    </span>
                </label>
                <x-input-error for="terms" />
            </fieldset>

            <x-safety-warning />

            <x-recaptcha />

            <x-submit class="w-full btn-lg" loading="অ্যাকাউন্ট খোলা হচ্ছে...">
                <x-lucide name="heart-handshake" class="size-5" /> ডোনার অ্যাকাউন্ট খুলুন
            </x-submit>
        </form>
        <x-slot:footer>আগেই অ্যাকাউন্ট আছে? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">লগইন করুন</a></x-slot:footer>
    </x-auth-card>
</x-layouts.app>
