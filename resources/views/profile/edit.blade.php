<x-layouts.app title="প্রোফাইল সম্পাদনা">
    <x-page-header title="প্রোফাইল সম্পাদনা" subtitle="আপনার তথ্য, এলাকা, ছবি ও পাসওয়ার্ড এখান থেকে বদলান।" icon="settings" />

    <div class="container-page grid gap-6 py-8 lg:grid-cols-[220px_1fr]">
        <nav class="hidden lg:block" aria-label="প্রোফাইলের অংশ">
            <ul class="sticky top-24 space-y-1 text-sm">
                @foreach (['details' => 'তথ্য ও এলাকা', 'photo' => 'প্রোফাইল ছবি', 'password' => 'পাসওয়ার্ড', 'danger' => 'অ্যাকাউন্ট বন্ধ / মুছুন'] as $id => $label)
                    <li><a href="#{{ $id }}" class="nav-link block">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="space-y-6">
            {{-- Personal details & location --}}
            <section id="details" class="card scroll-mt-24">
                <form method="POST" action="{{ route('profile.update') }}" x-data="submitting" @submit="submit" class="card-body space-y-6">
                    @csrf @method('PATCH')
                    <div>
                        <h2 class="text-lg font-bold">আপনার তথ্য ও এলাকা</h2>
                        <p class="text-sm text-slate-600">মোবাইল নম্বর বদলালে নতুন নম্বরটি আবার যাচাই করতে হবে।</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="label">পুরো নাম</label>
                            <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="input @error('name') input-error @enderror">
                            <x-input-error for="name" />
                        </div>
                        <div>
                            <label for="email" class="label">ইমেইল</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input @error('email') input-error @enderror">
                            <x-input-error for="email" />
                        </div>
                        <div>
                            <label for="phone" class="label">মোবাইল নম্বর</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required class="input @error('phone') input-error @enderror">
                            <x-input-error for="phone" />
                        </div>
                    </div>
                    <div>
                        <span class="label">রক্তের গ্রুপ</span>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                            @foreach ($bloodGroups as $bg)
                                <label class="cursor-pointer">
                                    <input type="radio" name="blood_group_id" value="{{ $bg->id }}" class="peer sr-only" @checked(old('blood_group_id', $profile?->blood_group_id) == $bg->id) required>
                                    <span class="block rounded-xl border border-slate-200 py-2 text-center text-sm font-bold transition peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600">{{ $bg->code }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error for="blood_group_id" />
                    </div>
                    <x-basics-fields :date-of-birth="$profile?->date_of_birth?->toDateString()" :weight="$profile?->weight_kg" />
                    <x-location-fields :divisions="$divisions" with-area :values="[
                        'division' => old('division_id', $profile?->division_id), 'district' => old('district_id', $profile?->district_id),
                        'upazila' => old('upazila_id', $profile?->upazila_id), 'areaName' => old('area_name', $profile?->area?->name),
                    ]" />
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold">সোশ্যাল মিডিয়া</h3>
                        <x-social-fields :facebook="$profile?->facebook_url" :whatsapp="$profile?->whatsapp_number" />
                    </div>
                    <div>
                        <label for="bio" class="label">যিনি রক্ত চান, তাঁর জন্য ছোট নোট <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                        <textarea id="bio" name="bio" rows="3" maxlength="500" class="input" placeholder="যেমন: শুক্রবার ও শনিবার সুবিধা। ধানমন্ডির হাসপাতাল হলে ভালো হয়।">{{ old('bio', $profile?->bio) }}</textarea>
                        <p class="hint">এখানে মোবাইল নম্বর বা বাসার ঠিকানা লিখবেন না।</p>
                        <x-input-error for="bio" />
                    </div>
                    <div class="flex justify-end"><x-submit loading="সংরক্ষণ হচ্ছে...">সংরক্ষণ করুন</x-submit></div>
                </form>
            </section>

            {{-- Photo --}}
            <section id="photo" class="card scroll-mt-24">
                <div class="card-body flex flex-col gap-5 sm:flex-row sm:items-center">
                    <x-avatar :user="$user" class="size-20 text-xl" />
                    <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" x-data="submitting" @submit="submit" class="flex-1 space-y-3">
                        @csrf
                        <h2 class="text-lg font-bold">প্রোফাইল ছবি</h2>
                        <input name="photo" type="file" accept="image/jpeg,image/png,image/webp" required
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold hover:file:bg-slate-200">
                        <x-input-error for="photo" />
                        <div class="flex gap-2">
                            <x-submit variant="btn-dark btn-sm" loading="আপলোড হচ্ছে..."><x-lucide name="upload" class="size-3.5" /> আপলোড করুন</x-submit>
                        </div>
                    </form>
                    @if ($user->profile_photo_path)
                        <form method="POST" action="{{ route('profile.photo.destroy') }}">
                            @csrf @method('DELETE')
                            <button class="btn-ghost btn-sm text-brand-700"><x-lucide name="trash-2" class="size-3.5" /> ছবি সরান</button>
                        </form>
                    @endif
                </div>
            </section>

            {{-- Password --}}
            <section id="password" class="card scroll-mt-24">
                <form method="POST" action="{{ route('profile.password') }}" x-data="submitting" @submit="submit" class="card-body space-y-4">
                    @csrf @method('PUT')
                    <h2 class="text-lg font-bold">পাসওয়ার্ড বদলান</h2>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="current_password" class="label">বর্তমান পাসওয়ার্ড</label>
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
                            <x-input-error for="current_password" bag="password" />
                        </div>
                        <div>
                            <label for="new_password" class="label">নতুন পাসওয়ার্ড</label>
                            <input id="new_password" name="password" type="password" required autocomplete="new-password" class="input">
                            <x-input-error for="password" bag="password" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="label">আবার লিখুন</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                        </div>
                    </div>
                    <div class="flex justify-end"><x-submit variant="btn-dark" loading="বদলানো হচ্ছে...">পাসওয়ার্ড বদলান</x-submit></div>
                </form>
            </section>

            {{-- Danger zone --}}
            <section id="danger" class="card scroll-mt-24 border-brand-200">
                <div class="card-body space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-brand-800">অ্যাকাউন্ট বন্ধ বা মুছে ফেলা</h2>
                        <p class="text-sm text-slate-600">বন্ধ করলে সাথে সাথে আপনি তালিকা থেকে সরে যাবেন, আবার লগইন করলে চালু হবে। মুছে ফেলার অনুরোধ ৭ দিনের মধ্যে সম্পন্ন করা হয়।</p>
                    </div>

                    <form method="POST" action="{{ route('profile.deactivate') }}" x-data="submitting" @submit="if (! confirm('অ্যাকাউন্ট বন্ধ করবেন? আপনাকে লগ আউট করা হবে।')) { $event.preventDefault(); return } submit($event)"
                          class="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1fr_auto] sm:items-end">
                        @csrf
                        <div>
                            <label for="deactivate_password" class="label">অ্যাকাউন্ট বন্ধ করুন — পাসওয়ার্ড দিয়ে নিশ্চিত করুন</label>
                            <input id="deactivate_password" name="password" type="password" required class="input">
                            <x-input-error for="password" bag="deactivate" />
                        </div>
                        <x-submit variant="btn-outline" loading="বন্ধ হচ্ছে..."><x-lucide name="power" /> বন্ধ করুন</x-submit>
                    </form>

                    @if ($pendingDeletion)
                        <p class="flex items-center gap-2 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                            <x-lucide name="clock" /> আপনার মুছে ফেলার অনুরোধ ({{ $pendingDeletion->type === 'account' ? 'পুরো অ্যাকাউন্ট' : 'ব্যক্তিগত তথ্য' }}) {{ $pendingDeletion->created_at->diffForHumans() }} পাঠানো হয়েছে, যাচাই চলছে।
                        </p>
                    @else
                        <form method="POST" action="{{ route('profile.deletion') }}" x-data="submitting" @submit="if (! confirm('মুছে ফেলার অনুরোধ পাঠাবেন? আপনার প্রোফাইল সাথে সাথে লুকিয়ে যাবে।')) { $event.preventDefault(); return } submit($event)"
                              class="space-y-3 rounded-xl border border-brand-200 bg-brand-50/40 p-4">
                            @csrf
                            <fieldset class="flex flex-wrap gap-4 text-sm">
                                <legend class="label">কী মুছতে চান</legend>
                                <label class="flex items-center gap-2"><input type="radio" name="type" value="account" class="checkbox rounded-full" checked> পুরো অ্যাকাউন্ট</label>
                                <label class="flex items-center gap-2"><input type="radio" name="type" value="personal_data" class="checkbox rounded-full"> শুধু ব্যক্তিগত তথ্য</label>
                            </fieldset>
                            <textarea name="reason" rows="2" maxlength="1000" class="input" placeholder="কারণ (ইচ্ছা হলে)"></textarea>
                            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                                <div>
                                    <label for="deletion_password" class="label">পাসওয়ার্ড দিয়ে নিশ্চিত করুন</label>
                                    <input id="deletion_password" name="password" type="password" required class="input">
                                    <x-input-error for="password" bag="deletion" />
                                </div>
                                <x-submit variant="btn-primary" loading="পাঠানো হচ্ছে..."><x-lucide name="trash-2" /> মুছে ফেলার অনুরোধ</x-submit>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
