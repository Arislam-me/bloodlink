<?php

namespace App\Rules;

use App\Support\SocialLinks;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Value must be a link to a Facebook profile or page (normalize with SocialLinks first). */
class FacebookProfileUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! SocialLinks::isFacebookProfile($value)) {
            $fail('আপনার ফেসবুক প্রোফাইলের সঠিক লিংক দিন, যেমন: facebook.com/your.name');
        }
    }
}
