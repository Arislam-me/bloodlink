@props(['donor'])
{{-- Public search card. Shows ONLY masked, non-contact information. --}}
<article class="card group flex flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-lift">
    <div class="flex items-start gap-4">
        <x-blood-badge :code="$donor->bloodGroup->code" />
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5">
                <h3 class="truncate text-base font-bold text-slate-900">{{ $donor->user->publicName() }}</h3>
                @if ($donor->user->verified_at)
                    <span title="BloodLink যাচাই করেছে" class="text-sky-600"><x-lucide name="badge-check" class="size-4" /><span class="sr-only">যাচাইকৃত ডোনার</span></span>
                @endif
            </div>
            <p class="mt-0.5 flex items-center gap-1 truncate text-sm text-slate-600">
                <x-lucide name="map-pin" class="size-3.5 text-slate-400" /> {{ $donor->generalLocation() }}
            </p>
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
        <div class="rounded-xl bg-slate-50 p-2.5">
            <dt class="text-slate-500">অবস্থা</dt>
            <dd class="mt-0.5 flex items-center gap-1.5 font-semibold">
                @if ($donor->is_available)
                    <span class="size-2 rounded-full bg-emerald-500"></span><span class="text-emerald-800">রক্ত দিতে প্রস্তুত</span>
                @else
                    <span class="size-2 rounded-full bg-slate-400"></span><span class="text-slate-600">{{ $donor->inCooldown() ? 'বিশ্রামে আছেন' : 'এখন পারবেন না' }}</span>
                @endif
            </dd>
        </div>
        <div class="rounded-xl bg-slate-50 p-2.5">
            <dt class="text-slate-500">শেষ রক্তদান</dt>
            <dd class="mt-0.5 font-semibold text-slate-800">{{ $donor->last_donation_date?->translatedFormat('F Y') ?? 'জানানো হয়নি' }}</dd>
        </div>
    </dl>

    <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-4">
        <span @class(['badge', 'bg-sky-50 text-sky-800 ring-sky-200' => $donor->user->verified_at, 'bg-slate-50 text-slate-600 ring-slate-200' => ! $donor->user->verified_at])>
            {{ $donor->user->verified_at ? 'যাচাইকৃত' : 'নিজের দেওয়া তথ্য' }}
        </span>
        <a href="{{ route('donors.show', $donor) }}" class="btn-primary btn-sm">
            <x-lucide name="phone" class="size-3.5" /> নম্বর দেখুন
        </a>
    </div>
</article>
