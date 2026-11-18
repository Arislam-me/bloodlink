<x-layouts.app title="ড্যাশবোর্ড">
    <x-page-header :title="'আসসালামু আলাইকুম, '.Str::before($user->name, ' ')" subtitle="আপনার রক্তদান, অনুরোধ ও প্রোফাইল এক জায়গায়।" icon="layout-dashboard">
        <a href="{{ route('donors.index') }}" class="btn-outline"><x-lucide name="search" /> ডোনার খুঁজুন</a>
        <a href="{{ route('emergency.create') }}" class="btn-primary"><x-lucide name="plus" /> রক্ত চাই</a>
    </x-page-header>

    <div class="container-page grid gap-6 py-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Donor status card --}}
            @if ($profile)
                <section class="card overflow-hidden">
                    <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
                        <x-blood-badge :code="$profile->bloodGroup->code" size="lg" />
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold">আপনার ডোনার প্রোফাইল</h2>
                                @if ($user->isVerifiedDonor())
                                    <span class="badge bg-sky-50 text-sky-800 ring-sky-200"><x-lucide name="badge-check" class="size-3.5" /> যাচাইকৃত ডোনার</span>
                                @else
                                    <span class="badge bg-slate-50 text-slate-600 ring-slate-200">অ্যাডমিনের যাচাইয়ের অপেক্ষায়</span>
                                @endif
                            </div>
                            <p class="mt-1 flex items-center gap-1 text-sm text-slate-600"><x-lucide name="map-pin" class="size-3.5" />
                                {{ collect([$profile->area?->name, $profile->upazila->name, $profile->district->name])->filter()->implode(', ') }}</p>
                            <p class="mt-1 text-sm text-slate-600">
                                শেষ রক্তদান: <strong>{{ $profile->last_donation_date?->translatedFormat('j F Y') ?? 'জানানো হয়নি' }}</strong>
                                @if ($profile->daysSinceLastDonation() !== null) ({{ $profile->daysSinceLastDonation() }} দিন আগে) @endif
                            </p>
                        </div>
                    </div>

                    {{-- Availability is automatic: resting for 4 months after a donation, available otherwise. --}}
                    @if ($profile->inCooldown())
                        <div class="flex flex-col gap-3 border-t border-slate-100 bg-sky-50/70 p-6 sm:flex-row sm:items-center">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700"><x-lucide name="calendar-clock" class="size-5" /></span>
                            <div class="flex-1">
                                <p class="font-semibold text-sky-900">রক্তদানের পর বিশ্রামে আছেন</p>
                                <p class="text-sm text-sky-800">
                                    রক্ত দেওয়ার জন্য ধন্যবাদ! <strong>{{ $profile->available_from->translatedFormat('j F Y') }}</strong> পর্যন্ত
                                    ({{ (int) now()->startOfDay()->diffInDays($profile->available_from) }} দিন) আপনাকে বিশ্রামে দেখানো হবে। এরপর নিজে থেকেই আবার "প্রস্তুত" হয়ে যাবেন।
                                </p>
                            </div>
                        </div>
                    @elseif ($profile->is_available)
                        <div class="flex items-center gap-3 border-t border-slate-100 bg-emerald-50/60 p-6">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700"><x-lucide name="circle-check" class="size-5" /></span>
                            <div>
                                <p class="font-semibold text-emerald-900">আপনি রক্ত দিতে প্রস্তুত</p>
                                <p class="text-sm text-emerald-800">কাছে কারও আপনার গ্রুপের রক্ত দরকার হলে আপনাকে জানানো হবে। রক্ত দিলে "পূর্বের রক্তদান"-এ যোগ করুন।</p>
                            </div>
                        </div>
                    @endif
                </section>
            @else
                <x-empty-state icon="droplet" title="ডোনার প্রোফাইল পূরণ করুন" description="রক্তের গ্রুপ ও এলাকা দিলে আপনি ডোনারের তালিকায় দেখা যাবেন।">
                    <a href="{{ route('profile.edit') }}" class="btn-primary">প্রোফাইল পূরণ করুন</a>
                </x-empty-state>
            @endif

            {{-- Stat tiles --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('donors.index') }}" class="card p-5 transition hover:shadow-lift">
                    <p class="text-xs font-medium text-slate-500">এই মাসে দেখা ডোনারের নম্বর</p>
                    <p class="mt-1 text-2xl font-extrabold">{{ $viewsThisMonth }} <span class="text-base font-semibold text-slate-400">জন</span></p>
                    <p class="mt-2 flex items-center gap-1 text-xs font-semibold text-brand-700">ডোনার খুঁজুন <x-lucide name="arrow-right" class="size-3" /></p>
                </a>
                <a href="{{ route('donations.index') }}" class="card p-5 transition hover:shadow-lift">
                    <p class="text-xs font-medium text-slate-500">মোট রক্তদান</p>
                    <p class="mt-1 text-2xl font-extrabold">{{ $donationCount }}</p>
                    <p class="mt-2 flex items-center gap-1 text-xs font-semibold text-brand-700">পূর্বের রক্তদান <x-lucide name="arrow-right" class="size-3" /></p>
                </a>
            </div>

            {{-- My emergency requests --}}
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="font-bold">আমার রক্তের অনুরোধ</h2>
                    <a href="{{ route('emergency.index', ['scope' => 'mine']) }}" class="text-sm font-semibold text-brand-700 hover:underline">সব দেখুন</a>
                </div>
                @forelse ($myRequests as $r)
                    <a href="{{ route('emergency.show', $r) }}" class="flex items-center gap-4 border-b border-slate-100 px-6 py-4 last:border-0 hover:bg-slate-50">
                        <x-blood-badge :code="$r->bloodGroup->code" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $r->hospital_name }}</p>
                            <p class="text-xs text-slate-500">{{ $r->generalLocation() }} · লাগবে {{ $r->needed_at->translatedFormat('j F, g:i A') }}</p>
                        </div>
                        <span class="badge {{ $r->status->badgeClasses() }}">{{ $r->status->getLabel() }}</span>
                    </a>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-500">আপনি এখনো কোনো রক্তের অনুরোধ দেননি।</p>
                @endforelse
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="flex items-center gap-2 font-bold"><x-lucide name="bell" class="size-4" /> নোটিফিকেশন @if($unreadCount)<span class="badge bg-brand-700 text-white ring-brand-700">{{ $unreadCount }}</span>@endif</h2>
                    <a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">সব</a>
                </div>
                @forelse ($notifications as $n)
                    <a href="{{ route('notifications.open', $n->id) }}" @class(['block border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50', 'bg-brand-50/40' => ! $n->read_at])>
                        <p class="text-sm font-semibold">{{ $n->data['title'] ?? 'নোটিফিকেশন' }}</p>
                        <p class="line-clamp-2 text-xs text-slate-600">{{ $n->data['body'] ?? '' }}</p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">এখনো কোনো নোটিফিকেশন নেই।</p>
                @endforelse
            </section>

            <section class="card card-body space-y-3">
                <h2 class="font-bold">অ্যাকাউন্ট</h2>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center justify-between"><span class="text-slate-600">মোবাইল {{ $user->maskedPhone() }}</span>
                        @if ($user->hasVerifiedPhone()) <span class="badge bg-emerald-50 text-emerald-800 ring-emerald-200">যাচাই হয়েছে</span>
                        @else <a href="{{ route('phone.verify.notice') }}" class="badge bg-amber-50 text-amber-800 ring-amber-200">যাচাই করুন</a> @endif
                    </li>
                </ul>
                <a href="{{ route('profile.edit') }}" class="btn-outline w-full"><x-lucide name="settings" /> প্রোফাইল সম্পাদনা</a>
            </section>

            <x-self-reported-notice />
        </aside>
    </div>
</x-layouts.app>
