@props(['type', 'target', 'label' => 'রিপোর্ট করুন'])
{{-- Abuse report dialog. $type: donor|emergency, $target: public ULID. --}}
<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-700">
        <x-lucide name="flag" class="size-3.5" /> {{ $label }}
    </button>

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 sm:items-center"
             @keydown.escape.window="open = false" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="report-title-{{ $target }}">
            <div class="card w-full max-w-md" @click.outside="open = false" x-trap.noscroll="open">
                @auth
                    <form method="POST" action="{{ route('reports.store') }}" x-data="submitting" @submit="submit" class="card-body space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="target" value="{{ $target }}">
                        <div class="flex items-start justify-between">
                            <h2 id="report-title-{{ $target }}" class="text-lg font-bold">সমস্যা জানান</h2>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-700" aria-label="বন্ধ করুন"><x-lucide name="x" class="size-5" /></button>
                        </div>
                        <p class="text-sm text-slate-600">আপনার রিপোর্ট গোপন রাখা হবে। আমাদের টিম প্রতিটি রিপোর্ট দেখে, নিয়ম ভাঙলে অ্যাকাউন্ট বন্ধ করা হয়।</p>
                        <div>
                            <label class="label" for="reason-{{ $target }}">কারণ</label>
                            <select id="reason-{{ $target }}" name="reason" class="input" required>
                                @foreach (\App\Enums\ReportReason::cases() as $reason)
                                    <option value="{{ $reason->value }}">{{ $reason->getLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label" for="details-{{ $target }}">বিস্তারিত <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                            <textarea id="details-{{ $target }}" name="details" rows="3" maxlength="1000" class="input" placeholder="কী হয়েছিল?"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="btn-ghost" @click="open = false">বাতিল</button>
                            <x-submit loading="পাঠানো হচ্ছে...">রিপোর্ট পাঠান</x-submit>
                        </div>
                    </form>
                @else
                    <div class="card-body text-center">
                        <p class="text-sm text-slate-700">রিপোর্ট করতে আগে লগইন করুন।</p>
                        <a href="{{ route('login') }}" class="btn-primary mt-4">লগইন</a>
                    </div>
                @endauth
            </div>
        </div>
    </template>
</div>
