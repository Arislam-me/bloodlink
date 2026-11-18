<x-layouts.app title="জরুরি রক্তের অনুরোধ">
    <x-page-header title="জরুরি রক্তের অনুরোধ" subtitle="রোগী ও হাসপাতালের চলমান অনুরোধ — সবচেয়ে নতুনটি সবার ওপরে। শুধু এখন রক্ত দিতে পারলে সাড়া দিন।" icon="siren">
        <a href="{{ route('emergency.create') }}" class="btn-primary"><x-lucide name="plus" /> অনুরোধ দিন</a>
    </x-page-header>

    <div class="container-page py-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            @auth
                <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1">
                    <a href="{{ route('emergency.index') }}" @class(['rounded-lg px-4 py-2 text-sm font-semibold', 'bg-brand-700 text-white' => $scope === 'active', 'text-slate-600' => $scope !== 'active'])>চলমান অনুরোধ</a>
                    <a href="{{ route('emergency.index', ['scope' => 'mine']) }}" @class(['rounded-lg px-4 py-2 text-sm font-semibold', 'bg-brand-700 text-white' => $scope === 'mine', 'text-slate-600' => $scope !== 'mine'])>আমার অনুরোধ</a>
                </div>
            @endauth
            <form method="GET" action="{{ route('emergency.index') }}" class="card grid flex-1 gap-3 p-4 sm:grid-cols-2 lg:max-w-4xl lg:grid-cols-[140px_1fr_auto]">
                @if ($scope === 'mine')<input type="hidden" name="scope" value="mine">@endif
                <div>
                    <label for="blood_group" class="label">রক্তের গ্রুপ</label>
                    <select id="blood_group" name="blood_group" class="input">
                        <option value="">সব</option>
                        @foreach ($bloodGroups as $bg)<option value="{{ $bg->id }}" @selected(($filters['blood_group'] ?? null) == $bg->id)>{{ $bg->code }}</option>@endforeach
                    </select>
                </div>
                <x-location-fields :divisions="$divisions" :required="false" compact cols="sm:grid-cols-3"
                    :names="['division' => 'division', 'district' => 'district', 'upazila' => 'upazila']"
                    :values="['division' => $filters['division'] ?? null, 'district' => $filters['district'] ?? null, 'upazila' => $filters['upazila'] ?? null]" />
                <div class="flex items-end gap-2">
                    <button class="btn-dark w-full"><x-lucide name="search" /> খুঁজুন</button>
                </div>
            </form>
        </div>

        @if ($requests->isEmpty())
            <x-empty-state icon="circle-check" :title="$scope === 'mine' ? 'আপনার কোনো অনুরোধ নেই' : 'কোনো চলমান অনুরোধ পাওয়া যায়নি'"
                description="আপনার বা পরিচিত কারও রক্ত দরকার হলে অনুরোধ দিন, কাছের মিল থাকা ডোনারদের জানানো হবে।">
                <a href="{{ route('emergency.create') }}" class="btn-primary"><x-lucide name="plus" /> অনুরোধ দিন</a>
            </x-empty-state>
        @else
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($requests as $request)
                    <x-emergency-card :request="$request" />
                @endforeach
            </div>
            <div class="mt-8">{{ $requests->links() }}</div>
        @endif
    </div>
</x-layouts.app>
