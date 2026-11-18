<?php

namespace App\Http\Requests;

use App\Enums\ContactMethod;
use App\Models\Upazila;
use App\Rules\BangladeshPhone;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EmergencyRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['contact_phone' => PhoneNumber::normalize($this->input('contact_phone')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'blood_group_id' => ['required', 'integer', 'exists:blood_groups,id'],
            'patient_condition' => ['nullable', 'string', 'max:150'],
            'hospital_name' => ['required', 'string', 'min:3', 'max:150'],
            'hospital_address' => ['required', 'string', 'min:5', 'max:255'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'upazila_id' => ['required', 'integer', 'exists:upazilas,id'],
            // Bangladesh bounding box (approx.) — rejects junk coordinates.
            'latitude' => ['nullable', 'numeric', 'between:20.3,26.8', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:87.9,92.8', 'required_with:latitude'],
            'units_needed' => ['required', 'integer', 'min:1', 'max:10'],
            'needed_at' => ['required', 'date', 'after:now', 'before:+30 days'],
            'contact_phone' => ['required', new BangladeshPhone],
            'details' => ['nullable', 'string', 'max:2000'],
            'confirm_genuine' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_genuine.accepted' => 'অনুরোধটি সত্যি কি না, তা নিশ্চিত করুন।',
            'needed_at.after' => 'রক্ত লাগার সময় অবশ্যই এখনকার পরের সময় হতে হবে।',
            'needed_at.before' => 'আগামী ৩০ দিনের মধ্যের একটি সময় দিন।',
            'contact_phone.required' => 'যোগাযোগের জন্য একটি মোবাইল নম্বর দিন।',
            'latitude.between' => 'ম্যাপে বাংলাদেশের ভেতরের কোনো জায়গা বেছে নিন।',
            'longitude.between' => 'ম্যাপে বাংলাদেশের ভেতরের কোনো জায়গা বেছে নিন।',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->hasAny(['district_id', 'upazila_id'])) {
                return;
            }
            $upazila = Upazila::find($this->integer('upazila_id'));
            if (! $upazila || $upazila->district_id !== $this->integer('district_id')) {
                $v->errors()->add('upazila_id', 'বেছে নেওয়া উপজেলাটি এই জেলার মধ্যে নেই।');
            }
        });
    }

    /**
     * Only model fields (strips confirm/honeypot). Donors reach the requester
     * by the given phone number, shown to signed-in verified members.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            ...collect($this->validated())->except(['confirm_genuine', 'website', 'division_id', 'district_id'])->all(),
            'contact_method' => ContactMethod::Phone->value,
        ];
    }
}
