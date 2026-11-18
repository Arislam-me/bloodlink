<?php

namespace App\Support;

/**
 * Bangladeshi mobile number helpers. Canonical storage format: 01XXXXXXXXX.
 */
final class PhoneNumber
{
    public const PATTERN = '/^01[3-9]\d{8}$/';

    /** Accepts "+880 1712-345678", "8801712345678", "01712 345678" ... */
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        // Convert Bangla digits to ASCII, then strip everything but digits.
        $input = strtr($input, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '88') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    public static function isValid(?string $phone): bool
    {
        return $phone !== null && preg_match(self::PATTERN, $phone) === 1;
    }

    public static function international(string $phone): string
    {
        return '+88'.$phone;
    }
}
