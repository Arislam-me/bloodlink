<?php

namespace App\Http\Requests\Concerns;

use App\Models\Upazila;
use Illuminate\Validation\Validator;

/**
 * Shared rules for the Division > District > Upazila > Area picker.
 * The server derives division/district from the upazila, but we still verify
 * the submitted chain is consistent so tampered forms get a clear error.
 */
trait ValidatesDonorLocation
{
    /** @return array<string, mixed> */
    protected function locationRules(): array
    {
        return [
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'upazila_id' => ['required', 'integer', 'exists:upazilas,id'],
            // Area / neighborhood is typed freely; matched to an existing area or created for admin review.
            'area_name' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pM\pN\s\-\.,\/#()]+$/u'],
        ];
    }

    protected function validateLocationChain(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->hasAny(['division_id', 'district_id', 'upazila_id'])) {
                return;
            }
            $upazila = Upazila::with('district')->find($this->integer('upazila_id'));
            if (! $upazila || $upazila->district_id !== $this->integer('district_id')
                || $upazila->district->division_id !== $this->integer('division_id')) {
                $v->errors()->add('upazila_id', 'বেছে নেওয়া উপজেলাটি এই জেলার মধ্যে নেই।');
            }
        });
    }
}
