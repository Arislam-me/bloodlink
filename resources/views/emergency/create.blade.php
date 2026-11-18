<x-layouts.app title="রক্তের অনুরোধ দিন">
    <x-page-header title="জরুরি রক্তের অনুরোধ দিন" subtitle="অনুরোধ প্রকাশ হলে একই জেলার মিল থাকা, প্রস্তুত ডোনারদের জানানো হবে।" icon="siren" />

    <div class="container-page grid gap-6 py-8 lg:grid-cols-3">
        <form method="POST" action="{{ route('emergency.store') }}" x-data="submitting" @submit="submit" class="space-y-6 lg:col-span-2">
            @csrf
            <div class="absolute -left-[9999px]" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

            <section class="card card-body space-y-5">
                <h2 class="flex items-center gap-2 text-lg font-bold"><x-lucide name="droplet" class="size-5 text-brand-700" /> কোন রক্ত লাগবে</h2>
                <div>
                    <span class="label">রক্তের গ্রুপ <span class="text-brand-700">*</span></span>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                        @foreach ($bloodGroups as $bg)
                            <label class="cursor-pointer">
                                <input type="radio" name="blood_group_id" value="{{ $bg->id }}" class="peer sr-only" @checked(old('blood_group_id') == $bg->id) required>
                                <span class="block rounded-xl border border-slate-200 py-2.5 text-center text-sm font-bold transition peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 hover:border-brand-300">{{ $bg->code }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error for="blood_group_id" />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="units_needed" class="label">কত ব্যাগ লাগবে <span class="text-brand-700">*</span></label>
                        <input id="units_needed" name="units_needed" type="number" min="1" max="10" value="{{ old('units_needed', 1) }}" required class="input @error('units_needed') input-error @enderror">
                        <x-input-error for="units_needed" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="needed_at" class="label">কবে, কখন লাগবে <span class="text-brand-700">*</span></label>
                        <input id="needed_at" name="needed_at" type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" max="{{ now()->addDays(30)->format('Y-m-d\TH:i') }}"
                               value="{{ old('needed_at') }}" required class="input @error('needed_at') input-error @enderror">
                        <x-input-error for="needed_at" />
                    </div>
                </div>
                <div>
                    <label for="patient_condition" class="label">কেন লাগবে / রোগীর অবস্থা <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                    <input id="patient_condition" name="patient_condition" maxlength="150" value="{{ old('patient_condition') }}" class="input" placeholder="যেমন: অপারেশন, থ্যালাসেমিয়া, দুর্ঘটনা, প্রসব">
                    <p class="hint">রোগীর পুরো নাম বা পরিচয় দেওয়ার দরকার নেই।</p>
                </div>
            </section>

            <section class="card card-body space-y-5">
                <h2 class="flex items-center gap-2 text-lg font-bold"><x-lucide name="hospital" class="size-5 text-brand-700" /> হাসপাতাল</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="hospital_name" class="label">হাসপাতালের নাম <span class="text-brand-700">*</span></label>
                        <input id="hospital_name" name="hospital_name" maxlength="150" value="{{ old('hospital_name') }}" required class="input @error('hospital_name') input-error @enderror">
                        <x-input-error for="hospital_name" />
                    </div>
                    <div>
                        <label for="hospital_address" class="label">হাসপাতালের ঠিকানা / ওয়ার্ড <span class="text-brand-700">*</span></label>
                        <input id="hospital_address" name="hospital_address" maxlength="255" value="{{ old('hospital_address') }}" required class="input @error('hospital_address') input-error @enderror" placeholder="যেমন: ৫ নম্বর ওয়ার্ড, বকশিবাজার">
                        <x-input-error for="hospital_address" />
                    </div>
                </div>
                <x-location-fields :divisions="$divisions" :values="['division' => old('division_id'), 'district' => old('district_id'), 'upazila' => old('upazila_id')]" />

                {{-- OpenStreetMap hospital pin --}}
                <div x-data="mapPicker(@js(['lat' => old('latitude'), 'lng' => old('longitude')]))" class="space-y-3">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <span class="label mb-0">ম্যাপে হাসপাতাল দেখান <span class="font-normal text-slate-500">(ইচ্ছা হলে, তবে ভালো হয়)</span></span>
                            <p class="hint">খুঁজুন, নিজের লোকেশন দিন অথবা ম্যাপে ক্লিক করুন। পিন টেনে ঠিক জায়গায় রাখুন।</p>
                        </div>
                        <button type="button" @click="locate()" class="btn-outline btn-sm"><x-lucide name="navigation" class="size-3.5" /> আমার লোকেশন</button>
                    </div>
                    <div class="relative">
                        <div class="flex gap-2">
                            <input type="search" x-model="query" @keydown.enter.prevent="search()" class="input" placeholder="হাসপাতাল খুঁজুন, যেমন: Dhaka Medical College">
                            <button type="button" @click="search()" class="btn-dark" :disabled="searching">
                                <span x-show="!searching"><x-lucide name="search" /></span><span x-show="searching" x-cloak><x-lucide name="loader-circle" class="animate-spin" /></span>
                                <span class="sr-only">ম্যাপে খুঁজুন</span>
                            </button>
                        </div>
                        <ul x-cloak x-show="results.length" class="absolute z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lift">
                            <template x-for="r in results" :key="r.place_id">
                                <li><button type="button" @click="choose(r)" class="block w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50" x-text="r.display_name"></button></li>
                            </template>
                        </ul>
                    </div>
                    <p x-cloak x-show="message" x-text="message" class="text-xs text-slate-600"></p>
                    <div x-ref="map" class="h-72 w-full rounded-xl border border-slate-200 bg-slate-100" role="application" aria-label="হাসপাতালের জায়গা বেছে নেওয়ার ম্যাপ"></div>
                    <input type="hidden" name="latitude" :value="lat">
                    <input type="hidden" name="longitude" :value="lng">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span x-text="lat ? `পিন বসানো হয়েছে: ${Number(lat).toFixed(5)}, ${Number(lng).toFixed(5)}` : 'এখনো পিন বসানো হয়নি'"></span>
                        <button type="button" x-show="lat" @click="clear()" class="font-semibold text-brand-700">পিন সরান</button>
                    </div>
                    <x-input-error for="latitude" />
                </div>
            </section>

            <section class="card card-body space-y-5">
                <h2 class="flex items-center gap-2 text-lg font-bold"><x-lucide name="phone" class="size-5 text-brand-700" /> যোগাযোগ</h2>
                <div>
                    <label for="contact_phone" class="label">যোগাযোগের মোবাইল নম্বর <span class="text-brand-700">*</span></label>
                    <input id="contact_phone" name="contact_phone" type="tel" inputmode="numeric" value="{{ old('contact_phone', auth()->user()->phone) }}" required class="input @error('contact_phone') input-error @enderror" placeholder="01XXXXXXXXX">
                    <p class="hint">ডোনাররা এই নম্বরে ফোন করবেন। নম্বরটি শুধু লগইন করা, মোবাইল যাচাই করা সদস্যরা দেখতে পাবেন।</p>
                    <x-input-error for="contact_phone" />
                </div>
                <div>
                    <label for="details" class="label">আরও কিছু জানাতে চান <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                    <textarea id="details" name="details" rows="4" maxlength="2000" class="input" placeholder="ডোনারদের জন্য দরকারি কথা, যেমন হাসপাতালে কার সাথে দেখা করবেন।">{{ old('details') }}</textarea>
                </div>
                <label class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 text-sm">
                    <input type="checkbox" name="confirm_genuine" value="1" required class="checkbox mt-0.5" @checked(old('confirm_genuine'))>
                    <span>আমি নিশ্চিত করছি যে এটি একটি সত্যিকারের প্রয়োজন, এবং রক্ত পেলে বা দরকার না থাকলে অনুরোধটি বন্ধ করে দেব। ভুয়া অনুরোধ দিলে অ্যাকাউন্ট বন্ধ করা হবে।</span>
                </label>
                <x-input-error for="confirm_genuine" />
            </section>

            <div class="flex justify-end">
                <x-submit class="btn-lg w-full sm:w-auto" loading="প্রকাশ হচ্ছে..."><x-lucide name="send" class="size-5" /> অনুরোধ প্রকাশ করুন</x-submit>
            </div>
        </form>

        <aside class="space-y-6">
            <x-safety-warning />
            <div class="card card-body space-y-3 text-sm text-slate-700">
                <h2 class="font-bold text-slate-900">এরপর কী হবে</h2>
                <ol class="space-y-3">
                    <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-700 text-xs font-bold text-white">১</span> আপনার অনুরোধ সাথে সাথে প্রকাশ হয়।</li>
                    <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-700 text-xs font-bold text-white">২</span> একই জেলার মিল থাকা, প্রস্তুত ডোনাররা খবর পান।</li>
                    <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-700 text-xs font-bold text-white">৩</span> ডোনাররা আপনাকে ফোন করেন বা "আমি রক্ত দিতে পারব" জানান।</li>
                    <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-700 text-xs font-bold text-white">৪</span> রক্ত লাগার সময়ের {{ config('bloodlink.emergency_expiry_grace_hours') }} ঘণ্টা পর অনুরোধটি নিজে থেকেই বন্ধ হয়ে যায়।</li>
                </ol>
            </div>
            <div class="card card-body border-amber-200 bg-amber-50 text-sm text-amber-900">
                <p class="flex items-center gap-2 font-bold"><x-lucide name="triangle-alert" /> জীবন ঝুঁকিতে?</p>
                <p class="mt-1">এখনই <strong>৯৯৯</strong>-এ ফোন করুন এবং হাসপাতালের ব্লাড ব্যাংকে সরাসরি যোগাযোগ করুন। অনলাইনের সাড়ার জন্য অপেক্ষা করবেন না।</p>
            </div>
        </aside>
    </div>
</x-layouts.app>
