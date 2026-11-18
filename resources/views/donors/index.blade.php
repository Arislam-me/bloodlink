<x-layouts.app title="রক্তদাতা খুঁজুন">
    <x-page-header title="রক্তদাতা খুঁজুন" subtitle="রক্তের গ্রুপ ও এলাকা দিয়ে খুঁজুন। মোবাইল যাচাই করা সদস্যরা ডোনারের নম্বর দেখতে পারেন।" icon="search" />

    <div class="container-page grid gap-6 py-8 lg:grid-cols-[300px_1fr]">
        {{-- ============================ Filters ============================ --}}
        <aside x-data="{ open: false }">
            <button type="button" class="btn-outline w-full lg:hidden" @click="open = !open" :aria-expanded="open">
                <x-lucide name="sliders-horizontal" /> ফিল্টার
            </button>
            <form method="GET" action="{{ route('donors.index') }}" x-data="submitting" @submit="submit"
                  class="card card-body mt-3 space-y-5 lg:sticky lg:top-24 lg:mt-0 lg:block" :class="open ? 'block' : 'hidden'">
                <fieldset>
                    <legend class="label">রক্তের গ্রুপ</legend>
                    <div class="grid grid-cols-4 gap-1.5">
                        <label class="col-span-4 cursor-pointer">
                            <input type="radio" name="blood_group" value="" class="peer sr-only" @checked(empty($filters['blood_group']))>
                            <span class="block rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white">সব গ্রুপ</span>
                        </label>
                        @foreach ($bloodGroups as $bg)
                            <label class="cursor-pointer">
                                <input type="radio" name="blood_group" value="{{ $bg->id }}" class="peer sr-only" @checked(($filters['blood_group'] ?? null) == $bg->id)>
                                <span class="block rounded-lg border border-slate-200 py-2 text-center text-sm font-bold transition peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 hover:border-brand-300">{{ $bg->code }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <x-location-fields :divisions="$divisions" :required="false" compact with-area cols="grid-cols-1"
                    :names="['division' => 'division', 'district' => 'district', 'upazila' => 'upazila', 'area' => 'area']"
                    :values="['division' => $filters['division'] ?? null, 'district' => $filters['district'] ?? null, 'upazila' => $filters['upazila'] ?? null, 'area' => $filters['area'] ?? null]" />

                <div>
                    <label for="availability" class="label">অবস্থা</label>
                    <select id="availability" name="availability" class="input">
                        <option value="any" @selected($filters['availability'] === 'any')>সব ডোনার</option>
                        <option value="available" @selected($filters['availability'] === 'available')>এখন প্রস্তুত</option>
                        <option value="unavailable" @selected($filters['availability'] === 'unavailable')>এখন পারবেন না / বিশ্রামে</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="verified" value="1" class="checkbox" @checked($filters['verified'])> শুধু যাচাইকৃত ডোনার
                </label>
                <div class="flex gap-2">
                    <x-submit class="flex-1" loading="খোঁজা হচ্ছে..."><x-lucide name="search" /> খুঁজুন</x-submit>
                    <a href="{{ route('donors.index') }}" class="btn-ghost">মুছুন</a>
                </div>
            </form>
        </aside>

        {{-- ============================ Results ============================ --}}
        <section aria-labelledby="results-heading">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 id="results-heading" class="text-sm text-slate-600">
                    <strong class="text-slate-900">{{ number_format($donors->total()) }}</strong> জন ডোনার পাওয়া গেছে
                </h2>
                <form method="GET" action="{{ route('donors.index') }}" class="flex items-center gap-2">
                    @foreach (collect($filters)->except(['sort', 'page'])->filter(fn ($v) => $v !== null && $v !== false) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v === true ? 1 : $v }}">
                    @endforeach
                    <label for="sort" class="text-sm text-slate-600">সাজান</label>
                    <select id="sort" name="sort" class="input !w-auto !py-1.5" onchange="this.form.submit()">
                        <option value="relevance" @selected(($filters['sort'] ?? 'relevance') === 'relevance')>সবচেয়ে মানানসই</option>
                        <option value="last_donation" @selected(($filters['sort'] ?? null) === 'last_donation')>অনেকদিন রক্ত দেননি</option>
                        <option value="newest" @selected(($filters['sort'] ?? null) === 'newest')>নতুন ডোনার</option>
                    </select>
                    <noscript><button class="btn-outline btn-sm">প্রয়োগ</button></noscript>
                </form>
            </div>

            <x-self-reported-notice class="mb-5" />

            @if ($donors->isEmpty())
                <x-empty-state icon="search" title="এই ফিল্টারে কোনো ডোনার পাওয়া যায়নি"
                    description="পাশের উপজেলা বা পুরো জেলায় খুঁজে দেখুন, অথবা সব ডোনার দেখুন। চাইলে জরুরি অনুরোধ দিন — জেলার মিল থাকা ডোনারদের জানানো হবে।">
                    <a href="{{ route('donors.index', ['blood_group' => $filters['blood_group'] ?? null, 'district' => $filters['district'] ?? null, 'division' => $filters['division'] ?? null, 'availability' => 'any']) }}" class="btn-outline">আরও বড় এলাকায় খুঁজুন</a>
                    <a href="{{ route('emergency.create') }}" class="btn-primary"><x-lucide name="siren" /> জরুরি অনুরোধ দিন</a>
                </x-empty-state>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($donors as $donor)
                        <x-donor-card :donor="$donor" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $donors->onEachSide(1)->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.app>
