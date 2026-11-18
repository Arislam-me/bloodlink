@props([
    'divisions',
    'values' => [],          // ['division' => id, 'district' => id, 'upazila' => id, 'area' => id, 'areaName' => str]
    'names' => ['division' => 'division_id', 'district' => 'district_id', 'upazila' => 'upazila_id', 'area' => 'area_id'],
    'withArea' => false,
    'required' => true,
    'compact' => false,      // search-filter mode: "সব ..." options, area chosen from a list
    'cols' => 'sm:grid-cols-2 lg:grid-cols-3',
])
@php
    $pick = fn (string $label) => $required ? "{$label} বেছে নিন" : "সব {$label}";
    $listId = 'area-suggestions-'.Str::random(6);
@endphp
{{-- Dependent location selects backed by Alpine `locationPicker` (resources/js/components/location-picker.js). --}}
<div x-data="locationPicker(@js([
        'division' => $values['division'] ?? null,
        'district' => $values['district'] ?? null,
        'upazila' => $values['upazila'] ?? null,
        'area' => $values['area'] ?? null,
        'areaName' => $values['areaName'] ?? '',
        'withArea' => $withArea,
    ]))" {{ $attributes->merge(['class' => 'grid gap-4 '.$cols]) }}>

    <div>
        <label for="loc-division" class="label">বিভাগ @if($required)<span class="text-brand-700">*</span>@endif</label>
        <select id="loc-division" name="{{ $names['division'] }}" x-model="division" @change="loadDistricts()" @required($required)
                @class(['input', 'input-error' => $errors->has($names['division'])])>
            <option value="">{{ $pick('বিভাগ') }}</option>
            @foreach ($divisions as $d)
                <option value="{{ $d->id }}" @selected(($values['division'] ?? null) == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <x-input-error :for="$names['division']" />
    </div>

    <div>
        <label for="loc-district" class="label">জেলা @if($required)<span class="text-brand-700">*</span>@endif</label>
        <select id="loc-district" name="{{ $names['district'] }}" x-model="district" @change="loadUpazilas()" :disabled="!division || loading.districts" @required($required)
                @class(['input', 'input-error' => $errors->has($names['district'])])>
            <option value="" x-text="loading.districts ? 'লোড হচ্ছে...' : (division ? @js($pick('জেলা')) : 'আগে বিভাগ বেছে নিন')"></option>
            <template x-for="d in districts" :key="d.id">
                <option :value="String(d.id)" x-text="d.name" :selected="String(d.id) === district"></option>
            </template>
        </select>
        <x-input-error :for="$names['district']" />
    </div>

    <div>
        <label for="loc-upazila" class="label">উপজেলা / থানা @if($required)<span class="text-brand-700">*</span>@endif</label>
        <select id="loc-upazila" name="{{ $names['upazila'] }}" x-model="upazila" @change="loadAreas()" :disabled="!district || loading.upazilas" @required($required)
                @class(['input', 'input-error' => $errors->has($names['upazila'])])>
            <option value="" x-text="loading.upazilas ? 'লোড হচ্ছে...' : (district ? @js($pick('উপজেলা / থানা')) : 'আগে জেলা বেছে নিন')"></option>
            <template x-for="u in upazilas" :key="u.id">
                <option :value="String(u.id)" x-text="u.name + (u.is_city_thana ? ' (সিটি)' : '')" :selected="String(u.id) === upazila"></option>
            </template>
        </select>
        <x-input-error :for="$names['upazila']" />
    </div>

    @if ($withArea)
        @if ($compact)
            <div>
                <label for="loc-area" class="label">এলাকা</label>
                <select id="loc-area" name="{{ $names['area'] ?? 'area' }}" x-model="area" :disabled="!upazila || loading.areas" class="input">
                    <option value="">সব এলাকা</option>
                    <template x-for="a in areas" :key="a.id">
                        <option :value="String(a.id)" x-text="a.name" :selected="String(a.id) === area"></option>
                    </template>
                </select>
            </div>
        @else
            {{-- Free text: the user types their own area; known areas are offered as suggestions. --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label for="loc-area-name" class="label">এলাকা / মহল্লা <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
                <input id="loc-area-name" type="text" name="area_name" x-model="areaName" list="{{ $listId }}" maxlength="100" autocomplete="off"
                       :disabled="!upazila" @class(['input', 'input-error' => $errors->has('area_name')]) placeholder="যেমন: মিরপুর ১০, কলেজ রোড">
                <datalist id="{{ $listId }}">
                    <template x-for="a in areas" :key="a.id"><option :value="a.name"></option></template>
                </datalist>
                <p class="hint">নিজের এলাকার নাম লিখুন। শুধু এলাকার নাম দিন — বাসা বা রাস্তার নম্বর দেবেন না।</p>
                <x-input-error for="area_name" />
            </div>
        @endif
    @endif

    <p x-cloak x-show="error" x-text="error" class="text-xs font-medium text-brand-700 sm:col-span-full" role="alert"></p>
</div>
