<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDonorBasics;
use App\Http\Requests\Concerns\ValidatesDonorLocation;
use App\Http\Requests\Concerns\ValidatesSocialLinks;
use App\Rules\BangladeshPhone;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    use ValidatesDonorBasics, ValidatesDonorLocation, ValidatesSocialLinks;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('phone')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => preg_replace('/\s+/', ' ', trim((string) $this->input('name'))),
        ]);
        $this->normalizeSocialLinks();
    }

    public function rules(): array
    {
        $id = $this->user()->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\pM\s\.\'\-]+$/u'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['required', new BangladeshPhone, Rule::unique('users', 'phone')->ignore($id)],
            'blood_group_id' => ['required', 'integer', 'exists:blood_groups,id'],
            ...$this->locationRules(),
            ...$this->socialLinkRules(),
            ...$this->donorBasicsRules(),
            'bio' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'facebook_url.required' => 'ফেসবুক প্রোফাইলের লিংক দিতে হবে।',
            ...$this->donorBasicsMessages(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateLocationChain($validator);
    }
}
