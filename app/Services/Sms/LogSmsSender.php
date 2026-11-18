<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes SMS to storage/logs/laravel.log instead of sending.
 * Never use in production — set SMS_DRIVER=http with a real gateway.
 */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): bool
    {
        Log::info('[SMS] to '.$phone.': '.$message);

        return true;
    }
}
