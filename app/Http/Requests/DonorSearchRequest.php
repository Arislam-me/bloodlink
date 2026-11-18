<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Whitelisted, typed search filters. Unknown parameters are ignored. */
class DonorSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blood_group' => ['nullable', 'integer', 'exists:blood_groups,id'],
            'division' => ['nullable', 'integer', 'exists:divisions,id'],
            'district' => ['nullable', 'integer', 'exists:districts,id'],
            'upazila' => ['nullable', 'integer', 'exists:upazilas,id'],
            'area' => ['nullable', 'integer', 'exists:areas,id'],
            'availability' => ['nullable', 'in:any,available,unavailable'],
            'verified' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:relevance,last_donation,newest'],
            // Hard cap on pagination depth makes bulk scraping impractical.
            'page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        $data = $this->validated();
        $data['availability'] ??= 'any'; // no default filter: show all donors
        $data['verified'] = $this->boolean('verified');

        return $data;
    }
}
