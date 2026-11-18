<x-layouts.app title="পূর্বের রক্তদান">
    <x-page-header title="পূর্বের রক্তদান" subtitle="আপনার রক্তদানের হিসাব রাখুন। সবচেয়ে নতুন তারিখটিই আপনার শেষ রক্তদানের তারিখ হিসেবে দেখানো হয়।" icon="history" />

    <div class="container-page grid gap-6 py-8 lg:grid-cols-3">
        <section class="card lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="font-bold">আপনার রক্তদান</h2>
                <span class="text-sm text-slate-500">মোট {{ $donations->total() }} বার</span>
            </div>
            @if ($donations->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-500"><x-lucide name="droplets" class="size-7" /></span>
                    <p class="mt-4 font-semibold">এখনো কোনো রক্তদান যোগ করা হয়নি</p>
                    <p class="text-sm text-slate-500">পাশের ফর্ম থেকে প্রথম রক্তদানটি যোগ করুন।</p>
                </div>
            @else
                <ol class="divide-y divide-slate-100">
                    @foreach ($donations as $d)
                        <li class="px-6 py-4" x-data="{ asking: false }">
                            <div class="flex items-start gap-4">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-lucide name="droplet" class="size-5" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">{{ $d->donated_on->translatedFormat('j F Y') }}
                                        @if ($d->isVerified())
                                            <span class="badge ml-1 bg-sky-50 text-sky-800 ring-sky-200"><x-lucide name="badge-check" class="size-3" /> যাচাইকৃত</span>
                                        @else
                                            <span class="badge ml-1 bg-slate-50 text-slate-600 ring-slate-200">নিজের দেওয়া তথ্য</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-slate-600">{{ $d->location ?: 'জায়গার নাম দেওয়া হয়নি' }}</p>
                                    @if ($d->notes)<p class="mt-1 text-xs text-slate-500">{{ $d->notes }}</p>@endif
                                    @if ($d->hasRemovalRequest())
                                        <p class="mt-2 inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1 text-xs text-amber-900">
                                            <x-lucide name="clock" class="size-3.5" /> মুছে ফেলার অনুরোধ পাঠানো হয়েছে, অ্যাডমিনের সিদ্ধান্তের অপেক্ষায়
                                        </p>
                                    @endif
                                </div>
                                @can('requestRemoval', $d)
                                    <button type="button" @click="asking = !asking" class="btn-ghost btn-sm text-slate-500 hover:text-brand-700">
                                        <x-lucide name="trash-2" class="size-4" /> মুছতে অনুরোধ
                                    </button>
                                @endcan
                            </div>
                            @can('requestRemoval', $d)
                                <form x-cloak x-show="asking" method="POST" action="{{ route('donations.removal-request', $d) }}" x-data="submitting" @submit="submit" class="mt-3 space-y-2 rounded-xl bg-slate-50 p-3 sm:ml-14">
                                    @csrf
                                    <label class="label" for="reason-{{ $d->id }}">কেন মুছতে চান?</label>
                                    <input id="reason-{{ $d->id }}" name="reason" required minlength="5" maxlength="500" class="input" placeholder="যেমন: তারিখ ভুল দিয়েছিলাম">
                                    <x-input-error for="reason" />
                                    <div class="flex gap-2">
                                        <x-submit variant="btn-dark btn-sm" loading="পাঠানো হচ্ছে...">অনুরোধ পাঠান</x-submit>
                                        <button type="button" class="btn-ghost btn-sm" @click="asking = false">বাতিল</button>
                                    </div>
                                </form>
                            @endcan
                        </li>
                    @endforeach
                </ol>
                <div class="border-t border-slate-100 px-6 py-4">{{ $donations->links() }}</div>
            @endif
        </section>

        <aside class="space-y-6">
            <form method="POST" action="{{ route('donations.store') }}" x-data="submitting" @submit="submit" class="card card-body space-y-4">
                @csrf
                <h2 class="font-bold">রক্তদান যোগ করুন</h2>
                <p class="text-xs text-slate-600">রক্ত দেওয়ার পর {{ config('bloodlink.donation_cooldown_months') }} মাস আপনাকে নিজে থেকেই "বিশ্রামে" দেখানো হবে, এরপর আবার "প্রস্তুত" হয়ে যাবেন।</p>
                <div>
                    <label for="donated_on" class="label">তারিখ</label>
                    <input id="donated_on" type="date" name="donated_on" max="{{ now()->toDateString() }}" value="{{ old('donated_on') }}" required class="input @error('donated_on') input-error @enderror">
                    <x-input-error for="donated_on" />
                </div>
                <div>
                    <label for="location" class="label">হাসপাতাল / ব্লাড ব্যাংক <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                    <input id="location" name="location" maxlength="150" value="{{ old('location') }}" class="input" placeholder="যেমন: কোয়ান্টাম ব্লাড ল্যাব, শান্তিনগর">
                    <x-input-error for="location" />
                </div>
                <div>
                    <label for="notes" class="label">নোট <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                    <textarea id="notes" name="notes" rows="2" maxlength="500" class="input">{{ old('notes') }}</textarea>
                </div>
                <x-submit class="w-full" loading="সংরক্ষণ হচ্ছে..."><x-lucide name="plus" /> ইতিহাসে যোগ করুন</x-submit>
                <p class="text-xs text-slate-500">ভুল কিছু যোগ হয়ে গেলে "মুছতে অনুরোধ" করুন — অ্যাডমিন দেখে মুছে দেবেন।</p>
            </form>
            @if ($profile)
                <div class="card card-body text-sm">
                    <p class="text-slate-500">শেষ রক্তদানের তারিখ</p>
                    <p class="text-lg font-bold">{{ $profile->last_donation_date?->translatedFormat('j F Y') ?? 'জানানো হয়নি' }}</p>
                </div>
            @endif
            <x-self-reported-notice />
        </aside>
    </div>
</x-layouts.app>
