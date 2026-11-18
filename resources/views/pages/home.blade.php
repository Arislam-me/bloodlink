<x-layouts.app>
    {{-- ================================ Hero ================================ --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-white via-white to-sky-50/60">
        <div aria-hidden="true" class="pointer-events-none absolute -right-40 -top-40 size-[36rem] rounded-full bg-brand-100/60 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -left-32 bottom-0 size-80 rounded-full bg-sky-100/70 blur-3xl"></div>

        <div class="container-page relative grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-24">
            <div>
                <span class="badge bg-brand-50 text-brand-800 ring-brand-200"><x-lucide name="heart-pulse" class="size-3.5" /> এক ব্যাগ রক্ত, একটি জীবন</span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    দরকারের সময় <span class="text-brand-700">আপনার কাছেই</span> রক্তদাতা খুঁজুন
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-slate-600">
                    BloodLink রোগী ও তাঁর পরিবারকে নিজের উপজেলার স্বেচ্ছায় রক্তদাতাদের সাথে যুক্ত করে — দেশের ৬৪ জেলাতেই। আপনার তথ্য থাকে নিরাপদ।
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('emergency.create') }}" class="btn-primary btn-lg"><x-lucide name="siren" class="size-5" /> জরুরি রক্ত চাই</a>
                    <a href="{{ route('register') }}" class="btn-outline btn-lg"><x-lucide name="heart-handshake" class="size-5" /> ডোনার হিসেবে যুক্ত হোন</a>
                </div>
                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">
                    <li class="flex items-center gap-2"><x-lucide name="shield-check" class="size-4 text-emerald-600" /> নম্বর সবার জন্য খোলা নয়</li>
                    <li class="flex items-center gap-2"><x-lucide name="badge-check" class="size-4 text-sky-600" /> যাচাইকৃত ডোনার চিহ্ন</li>
                    <li class="flex items-center gap-2"><x-lucide name="lock" class="size-4 text-slate-500" /> নম্বর দেখতে মোবাইল যাচাই লাগে</li>
                </ul>
            </div>

            {{-- Quick search --}}
            <div class="card relative p-6 shadow-lift sm:p-8">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-700 text-white"><x-lucide name="search" class="size-5" /></span>
                    <div>
                        <h2 class="text-lg font-bold">রক্তদাতা খুঁজুন</h2>
                        <p class="text-sm text-slate-500">খোঁজার জন্য অ্যাকাউন্ট লাগে না, একদম ফ্রি।</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('donors.index') }}" class="mt-6 space-y-4">
                    <fieldset>
                        <legend class="label">রক্তের গ্রুপ</legend>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($bloodGroups as $bg)
                                <label class="cursor-pointer">
                                    <input type="radio" name="blood_group" value="{{ $bg->id }}" class="peer sr-only">
                                    <span class="block rounded-xl border border-slate-200 py-2.5 text-center text-sm font-bold text-slate-700 transition peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 hover:border-brand-300">{{ $bg->code }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-location-fields :divisions="$divisions" :required="false" compact cols="sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3"
                        :names="['division' => 'division', 'district' => 'district', 'upazila' => 'upazila']" />
                    <button class="btn-primary w-full btn-lg"><x-lucide name="search" class="size-5" /> ডোনার খুঁজুন</button>
                </form>
            </div>
        </div>
    </section>

    {{-- ================================ Stats ================================ --}}
    <section class="container-page -mt-2">
        <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['নিবন্ধিত ডোনার', $stats['donors'], 'users'],
                ['এখন প্রস্তুত', $stats['available'], 'activity'],
                ['পূরণ হওয়া অনুরোধ', $stats['fulfilled'], 'circle-check'],
                ['মোট রক্তদান', $stats['donations'], 'droplets'],
            ] as [$label, $value, $icon])
                <div class="card flex items-center gap-4 p-5">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-lucide :name="$icon" class="size-5" /></span>
                    <div>
                        <dd class="text-2xl font-extrabold tracking-tight">{{ number_format($value) }}</dd>
                        <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ========================== Latest requests ========================== --}}
    <section class="container-page mt-16">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight">সাম্প্রতিক জরুরি অনুরোধ</h2>
                <p class="mt-1 text-sm text-slate-600">এখন যাঁদের রক্ত দরকার — সবচেয়ে নতুনটি আগে।</p>
            </div>
            <a href="{{ route('emergency.index') }}" class="btn-ghost hidden sm:inline-flex">সব দেখুন <x-lucide name="arrow-right" /></a>
        </div>
        @if ($urgent->isEmpty())
            <x-empty-state class="mt-6" icon="circle-check" title="এখন কোনো জরুরি অনুরোধ নেই" description="ভালো খবর — এই মুহূর্তে কোনো অনুরোধ খোলা নেই। রক্ত দরকার হলে অনুরোধ দিন, কাছের ডোনারদের জানানো হবে।">
                <a href="{{ route('emergency.create') }}" class="btn-primary">অনুরোধ দিন</a>
            </x-empty-state>
        @else
            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($urgent as $request)
                    <x-emergency-card :request="$request" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- ============================ How it works ============================ --}}
    <section class="container-page mt-20">
        <div class="text-center">
            <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">BloodLink কীভাবে কাজ করে</h2>
            <p class="mx-auto mt-2 max-w-2xl text-slate-600">সহজ তিনটি ধাপ।</p>
        </div>
        <ol class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['search', 'এলাকায় খুঁজুন', 'রক্তের গ্রুপ, বিভাগ, জেলা, উপজেলা ও এলাকা দিয়ে ডোনার খুঁজুন। তালিকায় শুধু সাধারণ এলাকা দেখানো হয় — বাসার ঠিকানা বা নম্বর নয়।'],
                ['lock-open', 'নম্বর দেখুন', 'মোবাইল নম্বর যাচাই করা সদস্যরা ডোনারের প্রোফাইল খুলে মোবাইল নম্বর ও ফেসবুক দেখতে পারেন।'],
                ['heart-handshake', 'যোগাযোগ করুন', 'ডোনারকে ফোন করে কথা বলুন। রক্ত দেওয়ার আগে হাসপাতাল ডোনারকে পরীক্ষা করবে।'],
            ] as $i => [$icon, $title, $text])
                <li class="card relative p-6">
                    <span class="absolute right-5 top-5 text-5xl font-extrabold text-slate-100">{{ $i + 1 }}</span>
                    <span class="grid size-12 place-items-center rounded-2xl bg-brand-700 text-white"><x-lucide :name="$icon" class="size-6" /></span>
                    <h3 class="mt-5 text-lg font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ============================ Safety warning ============================ --}}
    <section class="container-page mt-12">
        <x-safety-warning />
    </section>

    {{-- ============================== CTA band ============================== --}}
    <section class="container-page mt-20">
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-12 text-white sm:px-12">
            <div aria-hidden="true" class="absolute -right-24 -top-24 size-80 rounded-full bg-brand-700/40 blur-3xl"></div>
            <div class="relative grid items-center gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">এক ব্যাগ রক্তে বাঁচতে পারে তিনটি জীবন।</h2>
                    <p class="mt-3 max-w-2xl text-slate-300">মাত্র দুই মিনিটে নিবন্ধন করুন। রক্ত দেওয়ার পর ৪ মাস আপনাকে নিজে থেকেই বিশ্রামে রাখা হবে।</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('register') }}" class="btn btn-lg bg-white text-slate-900 hover:bg-slate-100"><x-lucide name="user-plus" class="size-5" /> ডোনার হিসেবে নিবন্ধন</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
