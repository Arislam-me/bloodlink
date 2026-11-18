<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifies a Google reCAPTCHA v2 token server-side.
 * Passes automatically when reCAPTCHA is disabled (services.recaptcha.enabled = false).
 */
class Recaptcha implements ValidationRule
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** Run even when the field is missing from the request (bots simply omit it). */
    public bool $implicit = true;

    public static function enabled(): bool
    {
        return (bool) config('services.recaptcha.enabled');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::enabled()) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('"আমি রোবট নই" বক্সে টিক দিন।');

            return;
        }

        try {
            $response = Http::asForm()->timeout(8)->post(self::VERIFY_URL, [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if (! $response->successful() || $response->json('success') !== true) {
                $fail('যাচাই সফল হয়নি। "আমি রোবট নই" বক্সে আবার টিক দিন।');
            }
        } catch (Throwable $e) {
            Log::warning('reCAPTCHA verification failed: '.$e->getMessage());
            $fail('এখন যাচাই করা যাচ্ছে না। একটু পরে আবার চেষ্টা করুন।');
        }
    }
}
