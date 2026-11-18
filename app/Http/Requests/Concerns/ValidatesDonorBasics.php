<?php

namespace App\Http\Requests\Concerns;

/**
 * Date of birth and body weight, required on registration and profile edit.
 */
trait ValidatesDonorBasics
{
    /** @return array<string, mixed> */
    protected function donorBasicsRules(): array
    {
        return [
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1920-01-01'],
            'weight_kg' => ['required', 'integer', 'min:20', 'max:250'],
        ];
    }

    /** @return array<string, string> */
    protected function donorBasicsMessages(): array
    {
        return [
            'date_of_birth.required' => 'জন্মতারিখ দিতে হবে।',
            'date_of_birth.before' => 'জন্মতারিখ আজকের আগের হতে হবে।',
            'date_of_birth.after' => 'সঠিক জন্মতারিখ দিন।',
            'weight_kg.required' => 'ওজন (কেজি) দিতে হবে।',
            'weight_kg.min' => 'ওজন কমপক্ষে ২০ কেজি হতে হবে।',
            'weight_kg.max' => 'সঠিক ওজন (কেজি) দিন।',
        ];
    }
}
