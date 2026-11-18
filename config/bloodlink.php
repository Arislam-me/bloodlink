<?php

/*
|--------------------------------------------------------------------------
| BloodLink platform settings
|--------------------------------------------------------------------------
| Business rules live here so they can be tuned per environment without
| touching code. All limits are enforced server-side.
*/

return [

    // No limit on opening donor phone numbers. Admins are alerted (Filament bell) when a
    // member opens more than this many DIFFERENT donors in one calendar month (Asia/Dhaka).
    'view_alert_threshold' => (int) env('BLOODLINK_VIEW_ALERT_THRESHOLD', 50),

    // After a recorded donation the donor is automatically marked unavailable for
    // this many months, then switched back on by the scheduler. This is a platform
    // rest rule, NOT a medical eligibility determination (hospitals still screen).
    'donation_cooldown_months' => (int) env('BLOODLINK_DONATION_COOLDOWN_MONTHS', 4),

    'search_per_page' => 12,

    // Emergency requests auto-expire this many hours after the "needed at" time.
    'emergency_expiry_grace_hours' => 12,

    // Maximum active emergency requests one user may have at a time.
    'max_active_emergency_requests' => 3,

    // Cap on donors notified for a single emergency request.
    'max_notified_donors' => 200,

    // Pending contact requests expire after this many days.
    'contact_request_expiry_days' => 7,

    // Max contact requests a user may send per day.
    'daily_contact_request_limit' => 10,

    // Phone OTP settings.
    'otp' => [
        'length' => 6,
        'ttl_minutes' => 10,          // mobile SMS code
        'email_ttl_minutes' => 60,    // email code (matches the signed link lifetime)
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'), // log | http
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
    ],

    // Bump when the legal text changes.
    'legal_version' => '2026-11',

    // First administrator (used by AdminUserSeeder). Prefer `php artisan bloodlink:create-admin`.
    'admin' => [
        'name' => env('ADMIN_NAME', 'BloodLink Admin'),
        'email' => env('ADMIN_EMAIL'),
        'phone' => env('ADMIN_PHONE', '01700000000'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'seed_demo_data' => (bool) env('SEED_DEMO_DATA', false),

    'support_email' => env('SUPPORT_EMAIL', 'support@bloodlink.test'),
];
