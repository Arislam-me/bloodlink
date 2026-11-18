<?php

namespace App\Support;

/**
 * Normalizes and validates the social contact links on donor profiles.
 */
final class SocialLinks
{
    private const FACEBOOK_HOSTS = ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com', 'mbasic.facebook.com', 'fb.com', 'www.fb.com', 'fb.me'];

    /**
     * Turn "facebook.com/rahim", "fb.com/rahim" or a full URL into
     * "https://www.facebook.com/rahim". Returns the input unchanged when it is
     * not a Facebook link, so validation can reject it.
     */
    public static function normalizeFacebook(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        $withScheme = preg_match('#^https?://#i', $input) ? $input : 'https://'.$input;
        $parts = parse_url($withScheme);
        $host = strtolower($parts['host'] ?? '');

        if (! in_array($host, self::FACEBOOK_HOSTS, true)) {
            return $input;
        }

        $path = rtrim($parts['path'] ?? '', '/');
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return 'https://www.facebook.com'.$path.$query;
    }

    /** A normalized link pointing at a specific Facebook profile/page (not the home page). */
    public static function isFacebookProfile(?string $url): bool
    {
        if ($url === null || ! str_starts_with($url, 'https://www.facebook.com/')) {
            return false;
        }

        $rest = substr($url, strlen('https://www.facebook.com/'));

        return $rest !== '' && preg_match('#^[\pL\pN\.\-_/?=&%]+$#u', $rest) === 1;
    }

    /** wa.me link for a Bangladeshi number stored as 01XXXXXXXXX. */
    public static function whatsappUrl(?string $phone): ?string
    {
        return $phone ? 'https://wa.me/88'.$phone : null;
    }
}
