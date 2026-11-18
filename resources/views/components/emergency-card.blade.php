@props(['request'])
<article class="card flex flex-col p-5 transition hover:shadow-lift">
    <div class="flex items-start gap-4">
        <x-blood-badge :code="$request->bloodGroup->code" @class(['animate-pulse-ring' => $request->isOpen() && $request->isUrgent()]) />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge {{ $request->status->badgeClasses() }}">{{ $request->status->getLabel() }}</span>
                @if ($request->isOpen() && $request->isUrgent())
                    <span class="badge bg-brand-700 text-white ring-brand-700"><x-lucide name="siren" class="size-3" /> জরুরি</span>
                @endif
            </div>
            <h3 class="mt-1.5 truncate text-base font-bold text-slate-900">{{ $request->hospital_name }}</h3>
            <p class="flex items-center gap-1 truncate text-sm text-slate-600"><x-lucide name="map-pin" class="size-3.5 text-slate-400" /> {{ $request->generalLocation() }}</p>
        </div>
    </div>
    <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
        <div class="rounded-xl bg-slate-50 p-2.5">
            <dt class="text-slate-500">কত ব্যাগ লাগবে</dt>
            <dd class="mt-0.5 font-semibold text-slate-800">{{ $request->units_needed }} ব্যাগ</dd>
        </div>
        <div class="rounded-xl bg-slate-50 p-2.5">
            <dt class="text-slate-500">কখন লাগবে</dt>
            <dd class="mt-0.5 font-semibold text-slate-800">{{ $request->needed_at->translatedFormat('j F, g:i A') }}</dd>
        </div>
    </dl>
    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
        <span>{{ isset($request->responses_count) ? $request->responses_count.' জন ডোনার সাড়া দিয়েছেন' : $request->created_at->diffForHumans().' দেওয়া' }}</span>
        <a href="{{ route('emergency.show', $request) }}" class="btn-outline btn-sm">বিস্তারিত <x-lucide name="arrow-right" class="size-3.5" /></a>
    </div>
</article>
