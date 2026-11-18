<x-layouts.app :title="$emergency->hospital_name.'-এ '.$emergency->bloodGroup->code.' রক্ত দরকার'">
    <div class="container-page py-8">
        <a href="{{ route('emergency.index') }}" class="btn-ghost btn-sm mb-4"><x-lucide name="chevron-left" /> সব অনুরোধ</a>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center sm:p-8">
                        <x-blood-badge :code="$emergency->bloodGroup->code" size="lg" @class(['animate-pulse-ring' => $emergency->isOpen() && $emergency->isUrgent()]) />
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge {{ $emergency->status->badgeClasses() }}">{{ $emergency->status->getLabel() }}</span>
                                @if ($emergency->isOpen() && $emergency->isUrgent())
                                    <span class="badge bg-brand-700 text-white ring-brand-700"><x-lucide name="siren" class="size-3" /> জরুরি</span>
                                @endif
                                @if ($emergency->user->verified_at)
                                    <span class="badge bg-sky-50 text-sky-800 ring-sky-200"><x-lucide name="badge-check" class="size-3" /> যাচাইকৃত সদস্য</span>
                                @endif
                            </div>
                            <h1 class="mt-2 text-2xl font-extrabold tracking-tight">{{ $emergency->units_needed }} ব্যাগ {{ $emergency->bloodGroup->code }} রক্ত দরকার</h1>
                            <p class="mt-1 text-slate-600">{{ $emergency->hospital_name }} · {{ $emergency->generalLocation() }}</p>
                        </div>
                    </div>
                    <dl class="grid gap-px bg-slate-100 sm:grid-cols-3">
                        <div class="bg-white p-5"><dt class="text-xs text-slate-500">কখন লাগবে</dt><dd class="mt-1 font-bold">{{ $emergency->needed_at->translatedFormat('l, j F Y · g:i A') }}</dd></div>
                        <div class="bg-white p-5"><dt class="text-xs text-slate-500">অনুরোধ বন্ধ হবে</dt><dd class="mt-1 font-bold">{{ $emergency->expires_at->diffForHumans() }}</dd></div>
                        <div class="bg-white p-5"><dt class="text-xs text-slate-500">ডোনারের সাড়া</dt><dd class="mt-1 font-bold">{{ $responseCount }} জন</dd></div>
                    </dl>
                    <div class="space-y-5 p-6 sm:p-8">
                        <div>
                            <h2 class="text-sm font-bold">হাসপাতালের ঠিকানা</h2>
                            <p class="mt-1 text-sm text-slate-700">{{ $emergency->hospital_address }}, {{ $emergency->generalLocation() }}, {{ $emergency->division->name }}</p>
                        </div>
                        @if ($emergency->patient_condition)
                            <div><h2 class="text-sm font-bold">কেন লাগবে</h2><p class="mt-1 text-sm text-slate-700">{{ $emergency->patient_condition }}</p></div>
                        @endif
                        @if ($emergency->details)
                            <div><h2 class="text-sm font-bold">আরও তথ্য</h2><p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $emergency->details }}</p></div>
                        @endif
                        @if ($emergency->latitude && $emergency->longitude)
                            <div>
                                <div x-data="mapView({{ $emergency->latitude }}, {{ $emergency->longitude }}, @js($emergency->hospital_name))" class="h-64 w-full rounded-xl border border-slate-200 bg-slate-100" role="img" aria-label="{{ $emergency->hospital_name }}-এর ম্যাপ"></div>
                                <a href="{{ $emergency->osmUrl() }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:underline">OpenStreetMap-এ খুলুন <x-lucide name="external-link" class="size-3" /></a>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
                            <span>{{ $emergency->created_at->diffForHumans() }} দিয়েছেন {{ $emergency->user->publicName() }}</span>
                            @auth @unless ($isOwner)<x-report-modal type="emergency" :target="$emergency->public_id" label="অনুরোধটি রিপোর্ট করুন" />@endunless @endauth
                        </div>
                    </div>
                </div>

                {{-- Owner: donor responses --}}
                @if ($isOwner || auth()->user()?->isAdmin())
                    <div class="card">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="font-bold">যে ডোনাররা রক্ত দিতে রাজি ({{ $responses->count() }})</h2>
                            <p class="text-xs text-slate-500">ডোনারের প্রোফাইল খুলে নম্বর দেখুন। প্রোফাইল খুললে আপনার মাসিক সীমা থেকে ১টি গোনা হবে।</p>
                        </div>
                        @forelse ($responses as $res)
                            <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-4 last:border-0">
                                <x-avatar :user="$res->donor" masked />
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">{{ $res->donor->publicName() }}
                                        @if ($res->donor->donorProfile)<span class="badge ml-1 bg-brand-50 text-brand-800 ring-brand-200">{{ $res->donor->donorProfile->bloodGroup->code }}</span>@endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $res->donor->donorProfile?->upazila?->name }} · {{ $res->created_at->diffForHumans() }}</p>
                                    @if ($res->message)<p class="mt-1 text-sm text-slate-700">“{{ $res->message }}”</p>@endif
                                </div>
                                @if ($res->donor->donorProfile)
                                    <a href="{{ route('donors.show', $res->donor->donorProfile) }}" class="btn-outline btn-sm">দেখুন</a>
                                @endif
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">
                                {{ $emergency->status === \App\Enums\EmergencyStatus::Pending ? 'অ্যাডমিন আপনার অনুরোধটি যাচাই করছেন। অনুমোদনের পর ডোনারদের জানানো হবে।' : 'এখনো কেউ সাড়া দেননি। '.($emergency->notified_count ? 'কাছের '.$emergency->notified_count.' জন ডোনারকে জানানো হয়েছে।' : 'আরও ডোনারের কাছে পৌঁছাতে পেজটি শেয়ার করুন।') }}
                            </p>
                        @endforelse
                    </div>
                @endif
            </section>

            <aside class="space-y-6">
                @if ($isOwner)
                    <div class="card card-body space-y-3">
                        <h2 class="font-bold">আপনার অনুরোধ</h2>
                        @can('manage', $emergency)
                            <form method="POST" action="{{ route('emergency.fulfil', $emergency) }}" onsubmit="return confirm('রক্ত পেয়েছেন? অনুরোধটি পূরণ হয়েছে বলে চিহ্নিত করবেন?')">
                                @csrf
                                <button class="btn-primary w-full"><x-lucide name="circle-check" /> রক্ত পেয়েছি</button>
                            </form>
                            <form method="POST" action="{{ route('emergency.cancel', $emergency) }}" onsubmit="return confirm('অনুরোধটি বাতিল করবেন?')">
                                @csrf
                                <button class="btn-outline w-full"><x-lucide name="ban" /> অনুরোধ বাতিল করুন</button>
                            </form>
                        @else
                            <p class="text-sm text-slate-600">এই অনুরোধটি এখন "{{ $emergency->status->getLabel() }}", তাই আর বদলানো যাবে না।</p>
                        @endcan
                    </div>
                @elseif ($emergency->isOpen())
                    <div class="card card-body space-y-4">
                        <h2 class="font-bold">আপনি কি রক্ত দিতে পারবেন?</h2>
                        @guest
                            <p class="text-sm text-slate-600">সাড়া দিতে ও যোগাযোগের নম্বর দেখতে লগইন করুন।</p>
                            <a href="{{ route('login') }}" class="btn-primary w-full">লগইন করুন</a>
                        @else
                            @if ($emergency->contact_phone)
                                @if ($canViewContact)
                                    <div class="rounded-xl bg-emerald-50 p-4">
                                        <p class="text-xs text-emerald-800">যোগাযোগের নম্বর</p>
                                        <a href="tel:{{ $emergency->contact_phone }}" class="mt-1 flex items-center gap-2 text-lg font-extrabold text-emerald-900"><x-lucide name="phone" /> {{ $emergency->contact_phone }}</a>
                                        <a href="tel:{{ $emergency->contact_phone }}" class="btn-primary btn-sm mt-3 w-full"><x-lucide name="phone" class="size-3.5" /> এখনই ফোন করুন</a>
                                    </div>
                                @else
                                    <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-900">যোগাযোগের নম্বর দেখতে আগে আপনার মোবাইল নম্বর যাচাই করুন।</p>
                                @endif
                            @endif

                            @if ($hasResponded)
                                <p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-900"><x-lucide name="circle-check" class="mr-1" /> আপনি জানিয়েছেন যে রক্ত দিতে পারবেন। ধন্যবাদ!</p>
                            @elseif (auth()->user()->can('respond', $emergency))
                                <form method="POST" action="{{ route('emergency.respond', $emergency) }}" x-data="submitting" @submit="submit" class="space-y-3">
                                    @csrf
                                    <textarea name="message" rows="3" maxlength="500" class="input" placeholder="ইচ্ছা হলে কিছু লিখুন, যেমন: এক ঘণ্টার মধ্যে আসতে পারব।"></textarea>
                                    <x-submit class="w-full" loading="পাঠানো হচ্ছে..."><x-lucide name="hand" /> আমি রক্ত দিতে পারব</x-submit>
                                    <p class="text-xs text-slate-500">অনুরোধকারী আপনার ডোনার প্রোফাইল দেখতে পাবেন।</p>
                                </form>
                            @else
                                <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900">{{ Gate::inspect('respond', $emergency)->message() }}</p>
                            @endif
                        @endguest
                    </div>
                @endif
                <x-safety-warning />
                <x-self-reported-notice />
                <div class="card card-body text-sm text-slate-600">
                    <h2 class="flex items-center gap-2 font-bold text-slate-900"><x-lucide name="stethoscope" class="size-4" /> রক্ত দেওয়ার আগে</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>শুধু সুস্থ থাকলে রক্ত দিন, হাসপাতাল আগে পরীক্ষা করবে।</li>
                        <li>রক্তের জন্য কখনো টাকা লেনদেন করবেন না।</li>
                        <li>সন্দেহজনক অনুরোধ দেখলে রিপোর্ট করুন।</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
