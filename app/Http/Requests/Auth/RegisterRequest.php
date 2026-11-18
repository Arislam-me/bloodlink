<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\ValidatesDonorBasics;
use App\Http\Requests\Concerns\ValidatesDonorLocation;
use App\Http\Requests\Concerns\ValidatesSocialLinks;
use App\Rules\BangladeshPhone;
use App\Rules\Recaptcha;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    use ValidatesDonorBasics, ValidatesDonorLocation, ValidatesSocialLinks;

    public function authorize(): bool
    {
        return true;
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
        return [
            // \pM allows Bangla vowel signs (া ি ী ু ...), so Bangla names validate.
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\pM\s\.\'\-]+$/u'],
            'email' => ['required', 'string', 'email:rfc'.(app()->isProduction() ? ',dns' : ''), 'max:255', 'unique:users,email'],
            'phone' => ['required', new BangladeshPhone, 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'blood_group_id' => ['required', 'integer', 'exists:blood_groups,id'],
            ...$this->locationRules(),
            ...$this->socialLinkRules(),
            ...$this->donorBasicsRules(),
            'last_donation_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1950-01-01'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'],
            'terms' => ['accepted'],
            'g-recaptcha-response' => [new Recaptcha],
            // Honeypot: real users never see or fill this field.
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'নামে শুধু অক্ষর, স্পেস, ডট (.) ও হাইফেন (-) ব্যবহার করুন।',
            'terms.accepted' => 'নিবন্ধন করতে শর্তাবলি ও গোপনীয়তা নীতিতে সম্মতি দিন।',
            'website.max' => 'নিবন্ধন করা যায়নি।',
            'phone.unique' => 'এই মোবাইল নম্বর দিয়ে আগেই অ্যাকাউন্ট খোলা হয়েছে।',
            'facebook_url.required' => 'ফেসবুক প্রোফাইলের লিংক দিতে হবে।',
            ...$this->donorBasicsMessages(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateLocationChain($validator);
    }
}
