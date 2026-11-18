<x-layouts.app title="নোটিফিকেশন">
    <x-page-header title="নোটিফিকেশন" subtitle="জরুরি রক্তের খবর ও আপনার অনুরোধের আপডেট।" icon="bell">
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-outline"><x-lucide name="check" /> সব পড়া হয়েছে</button></form>
        @endif
    </x-page-header>
    <div class="container-page py-8">
        @if ($notifications->isEmpty())
            <x-empty-state icon="bell" title="নতুন কিছু নেই" description="কেউ আপনার সাহায্য চাইলে বা আপনার অনুরোধে সাড়া দিলে এখানে জানানো হবে।" />
        @else
            <div class="card divide-y divide-slate-100">
                @foreach ($notifications as $n)
                    @php $icon = ['emergency' => 'siren', 'emergency_response' => 'hand', 'available_again' => 'circle-check'][$n->data['kind'] ?? ''] ?? 'bell'; @endphp
                    <a href="{{ route('notifications.open', $n->id) }}" @class(['flex items-start gap-4 px-6 py-4 hover:bg-slate-50', 'bg-brand-50/40' => ! $n->read_at])>
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-lucide :name="$icon" class="size-5" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">{{ $n->data['title'] ?? 'নোটিফিকেশন' }}</p>
                            <p class="text-sm text-slate-600">{{ $n->data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($n->read_at)<span class="mt-2 size-2.5 rounded-full bg-brand-600" aria-label="নতুন"></span>@endunless
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-layouts.app>
