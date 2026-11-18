<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * DEVELOPMENT ONLY: show the latest mobile verification (OTP) code for one account,
 * read from storage/logs/laravel.log (where the "log" SMS driver writes).
 * Codes are stored hashed in the database, so the log is the only place they exist in plain text.
 */
#[Signature('bloodlink:codes {account : Account email address or mobile number}')]
#[Description('Dev only: print the latest mobile verification code for an account')]
class ShowDevCodes extends Command
{
    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('This command is disabled in production.');

            return self::FAILURE;
        }

        $account = trim($this->argument('account'));
        $user = str_contains($account, '@')
            ? User::withTrashed()->where('email', strtolower($account))->first()
            : User::withTrashed()->where('phone', PhoneNumber::normalize($account))->first();
        $log = storage_path('logs/laravel.log');

        if (! $user) {
            $this->error("No account found for {$account}.");

            return self::FAILURE;
        }
        if (! is_file($log)) {
            $this->error('Log file not found.');

            return self::FAILURE;
        }

        // Log line format: "[SMS] to 01XXXXXXXXX: <message containing the 6-digit code>".
        $smsCode = null;
        if (preg_match_all('/\[SMS\] to '.preg_quote($user->phone, '/').': \D*?(\d{6})/u', file_get_contents($log), $m)) {
            $smsCode = end($m[1]);
        }

        $this->table(['Mobile', 'Latest code', 'Status'], [
            [$user->phone, $smsCode ?? '-', $user->phone_verified_at ? 'already verified' : 'not verified'],
        ]);
        $this->line('Only the most recent code works; pressing "Resend" replaces it.');

        return self::SUCCESS;
    }
}
