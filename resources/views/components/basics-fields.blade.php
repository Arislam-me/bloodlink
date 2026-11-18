@props(['dateOfBirth' => null, 'weight' => null])
{{-- Date of birth and weight (kg), required on registration and profile edit. --}}
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="date_of_birth" class="label">জন্মতারিখ <span class="text-brand-700">*</span></label>
        <input id="date_of_birth" name="date_of_birth" type="date" required max="{{ now()->subDay()->toDateString() }}" min="1920-01-01"
               value="{{ old('date_of_birth', $dateOfBirth) }}" autocomplete="bday"
               @class(['input', 'input-error' => $errors->has('date_of_birth')])>
        <p class="hint">অন্যরা শুধু আপনার বয়স দেখবেন, পুরো জন্মতারিখ নয়।</p>
        <x-input-error for="date_of_birth" />
    </div>
    <div>
        <label for="weight_kg" class="label">ওজন (কেজি) <span class="text-brand-700">*</span></label>
        <input id="weight_kg" name="weight_kg" type="number" inputmode="numeric" required min="20" max="250" step="1"
               value="{{ old('weight_kg', $weight) }}" placeholder="যেমন: ৬০ কেজি হলে 60 লিখুন"
               @class(['input', 'input-error' => $errors->has('weight_kg')])>
        <x-input-error for="weight_kg" />
    </div>
</div>
