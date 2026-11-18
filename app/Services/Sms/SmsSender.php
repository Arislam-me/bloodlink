<?php

namespace App\Services\Sms;

interface SmsSender
{
    /** Send a plain-text SMS to a Bangladeshi number (01XXXXXXXXX). */
    public function send(string $phone, string $message): bool;
}
