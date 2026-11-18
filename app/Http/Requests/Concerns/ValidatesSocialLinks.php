<?php

namespace App\Http\Requests\Concerns;

use App\Rules\BangladeshPhone;
use App\Rules\FacebookProfileUrl;
use App\Support\PhoneNumber;
use App\Support\SocialLinks;

/**
 * Facebook profile link (required) and WhatsApp number (optional) for donor profiles.
 */
trait ValidatesSocialLinks
{
    /** Call from prepareForValidation(). */
    protected function normalizeSocialLinks(): void
    {
        $this->merge([
            'facebook_url' => SocialLinks::normalizeFacebook($this->input('facebook_url')),
            'whatsapp_number' => PhoneNumber::normalize($this->input('whatsapp_number')) ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    protected function socialLinkRules(): array
    {
        return [
            'facebook_url' => ['required', 'string', 'max:255', new FacebookProfileUrl],
            'whatsapp_number' => ['nullable', new BangladeshPhone],
        ];
    }
}
