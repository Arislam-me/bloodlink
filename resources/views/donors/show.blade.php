<x-layouts.app :title="'ডোনার '.$donor->user->publicName()">
    @php $u = $donor->user; @endphp
    <div class="container-page py-8">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('donors.index') }}" class="btn-ghost btn-sm mb-4"><x-lucide name="chevron-left" /> তালিকায় ফিরে যান</a>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- ======================= Public profile ======================= --}}
            <section class="card overflow-hidden lg:col-span-2">
                <div class="bg-gradient-to-r from-brand-700 to-brand-800 px-6 py-8 text-white sm:px-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <x-avatar :user="$u" :masked="! $unlocked" class="size-20 text-2xl ring-4 ring-white/30" />
                        <div class="flex-1">
                            <h1 class="flex items-center gap-2 text-2xl font-extrabold">
                                {{ $unlocked ? $u->name : $u->publicName() }}
                                @if ($u->verified_at)<x-lucide name="badge-check" class="size-6 text-sky-200" /><span class="sr-only">যাচাইকৃত ডোনার</span>@endif
                            </h1>
                            <p class="mt-1 flex items-center gap-1.5 text-brand-100"><x-lucide name="map-pin" class="size-4" />
                                {{ $unlocked && $donor->area ? $donor->area->name.', ' : '' }}{{ $donor->generalLocation() }}
                            </p>
                        </div>
                        <span class="grid size-20 place-items-center rounded-2xl bg-white text-3xl font-extrabold text-brand-700 shadow-lg">{{ $donor->bloodGroup->code }}</span>
                    </div>
                </div>

                <dl class="grid gap-px bg-slate-100 sm:grid-cols-3">
                    <div class="bg-white p-5">
                        <dt class="text-xs font-medium text-slate-500">অবস্থা</dt>
                        <dd class="mt-1 flex items-center gap-2 font-bold">
                            <span @class(['size-2.5 rounded-full', 'bg-emerald-500' => $donor->is_available, 'bg-slate-400' => ! $donor->is_available])></span>
                            {{ $donor->is_available ? 'রক্ত দিতে প্রস্তুত' : ($donor->inCooldown() ? 'রক্তদানের পর বিশ্রামে' : 'এখন পারবেন না') }}
                        </dd>
                        @if (! $donor->is_available && $donor->inCooldown())
                            <p class="mt-0.5 text-xs text-slate-500">{{ $donor->available_from->translatedFormat('F Y') }} থেকে আবার প্রস্তুত</p>
                        @endif
                    </div>
                    <div class="bg-white p-5">
                        <dt class="text-xs font-medium text-slate-500">শেষ রক্তদান (নিজের দেওয়া তথ্য)</dt>
                        <dd class="mt-1 font-bold">{{ $donor->last_donation_date?->translatedFormat('F Y') ?? 'জানানো হয়নি' }}</dd>
                    </div>
                    <div class="bg-white p-5">
                        <dt class="text-xs font-medium text-slate-500">মোট রক্তদান</dt>
                        <dd class="mt-1 font-bold">{{ $donationCount }} বার</dd>
                    </div>
                </dl>

                <div class="space-y-5 p-6 sm:p-8">
                    @if ($unlocked && ($donor->age() !== null || $donor->weight_kg))
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">বয়স</dt>
                                <dd class="mt-0.5 font-bold">{{ $donor->age() !== null ? $donor->age().' বছর' : 'জানানো হয়নি' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">ওজন</dt>
                                <dd class="mt-0.5 font-bold">{{ $donor->weight_kg ? $donor->weight_kg.' কেজি' : 'জানানো হয়নি' }}</dd>
                            </div>
                        </dl>
                    @endif
                    @if ($unlocked && $donor->bio)
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">ডোনারের নোট</h2>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $donor->bio }}</p>
                        </div>
                    @endif
                    <ul class="grid gap-3 text-sm sm:grid-cols-2">
                        <li class="flex items-center gap-2 text-slate-700"><x-lucide name="shield-check" class="size-4 text-emerald-600" />
                            {{ $u->verified_at ? 'BloodLink পরিচয় যাচাই করেছে' : 'BloodLink এখনো পরিচয় যাচাই করেনি' }}</li>
                        <li class="flex items-center gap-2 text-slate-700"><x-lucide name="calendar" class="size-4 text-slate-400" /> সদস্য হয়েছেন {{ $u->created_at->translatedFormat('F Y') }}</li>
                    </ul>
                    <x-self-reported-notice />
                    @auth
                        @unless ($isOwner)
                            <div class="text-right"><x-report-modal type="donor" :target="$donor->public_id" label="এই প্রোফাইল রিপোর্ট করুন" /></div>
                        @endunless
                    @endauth
                </div>
            </section>

            {{-- ======================= Contact panel ======================= --}}
            <aside class="space-y-6">
                @if ($isOwner)
                    <div class="card card-body">
                        <h2 class="font-bold">এটি আপনার প্রোফাইল</h2>
                        <p class="mt-1 text-sm text-slate-600">অন্যরা আপনাকে এভাবেই দেখে। যাঁরা আপনার প্রোফাইল খোলেন, তাঁরা আপনার পুরো নাম, এলাকা ও মোবাইল নম্বর দেখতে পান।</p>
                        <a href="{{ route('profile.edit') }}" class="btn-outline mt-4 w-full">প্রোফাইল সম্পাদনা</a>
                    </div>
                @elseif (! auth()->check())
                    <div class="card card-body text-center">
                        <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700"><x-lucide name="lock" class="size-6" /></span>
                        <h2 class="mt-3 font-bold">নম্বর দেখতে লগইন করুন</h2>
                        <p class="mt-1 text-sm text-slate-600">ডোনারদের সুরক্ষার জন্য শুধু মোবাইল যাচাই করা সদস্যরা নম্বর দেখতে পারেন।</p>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('login') }}" class="btn-primary">লগইন</a>
                            <a href="{{ route('register') }}" class="btn-outline">ফ্রি অ্যাকাউন্ট খুলুন</a>
                        </div>
                    </div>
                @elseif (! $unlocked)
                    <div class="card card-body">
                        <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700"><x-lucide name="phone" class="size-6" /></span>
                        <h2 class="mt-3 font-bold">ডোনারের নম্বর দেখুন</h2>
                        <p class="mt-1 text-sm text-slate-600">বোতাম চাপলে ডোনারের পুরো নাম, মোবাইল নম্বর ও ফেসবুক দেখতে পাবেন।</p>
                        @can('unlock', $donor)
                            <form method="POST" action="{{ route('donors.unlock', $donor) }}" x-data="submitting" @submit="submit" class="mt-4">
                                @csrf
                                <x-submit class="w-full" loading="খোলা হচ্ছে..."><x-lucide name="lock-open" /> নম্বর দেখুন</x-submit>
                            </form>
                        @else
                            <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">নম্বর দেখতে আগে আপনার মোবাইল নম্বর যাচাই করুন।</p>
                            <a href="{{ route('phone.verify.notice') }}" class="btn-dark mt-3 w-full">মোবাইল যাচাই করুন</a>
                        @endcan
                    </div>
                @else
                    {{-- Unlocked: phone number visible --}}
                    <div class="card card-body space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><x-lucide name="lock-open" class="size-5" /></span>
                            <h2 class="font-bold">ডোনারের যোগাযোগ</h2>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-4">
                            <p class="text-xs font-medium text-emerald-800">মোবাইল নম্বর</p>
                            <a href="tel:{{ $u->phone }}" class="mt-1 flex items-center gap-2 text-xl font-extrabold text-emerald-900"><x-lucide name="phone" class="size-5" /> {{ $u->phone }}</a>
                            <a href="tel:{{ $u->phone }}" class="btn-primary btn-sm mt-3 w-full"><x-lucide name="phone" class="size-3.5" /> এখনই ফোন করুন</a>
                        </div>
                        @if ($donor->facebook_url || $donor->whatsapp_number)
                            <div class="grid gap-2">
                                @if ($donor->facebook_url)
                                    <a href="{{ $donor->facebook_url }}" target="_blank" rel="noopener noreferrer nofollow" class="btn-outline btn-sm w-full">
                                        <x-lucide name="globe" class="size-3.5" /> ফেসবুক প্রোফাইল দেখুন
                                    </a>
                                @endif
                                @if ($donor->whatsapp_number)
                                    <a href="{{ \App\Support\SocialLinks::whatsappUrl($donor->whatsapp_number) }}" target="_blank" rel="noopener noreferrer nofollow" class="btn-outline btn-sm w-full">
                                        <x-lucide name="message-square" class="size-3.5" /> হোয়াটসঅ্যাপে মেসেজ ({{ $donor->whatsapp_number }})
                                    </a>
                                @endif
                            </div>
                        @endif
                        <ul class="space-y-1 text-xs text-slate-600">
                            <li>• ভদ্রভাবে কথা বলুন, রোগী ও হাসপাতালের তথ্য জানান।</li>
                            <li>• এই নম্বর অন্য কাউকে দেবেন না।</li>
                            <li>• রক্তের জন্য টাকা লেনদেন করবেন না।</li>
                        </ul>
                    </div>
                @endif

                <x-safety-warning />
            </aside>
        </div>
    </div>
</x-layouts.app>
