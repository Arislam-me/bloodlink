<?php

namespace App\Services\Sms;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Generic HTTP SMS gateway driver. Most Bangladeshi gateways (BulkSMSBD, SSL Wireless,
 * Alpha SMS, etc.) accept a POST with api key, sender id, number and message.
 * Adjust the payload keys in send() to match your provider's documentation.
 */
class HttpSmsSender implements SmsSender
{
    public function __construct(
        private readonly string $url,
        private readonly string $apiKey,
        private readonly ?string $senderId,
    ) {}

    public function send(string $phone, string $message): bool
    {
        try {
            $response = Http::timeout(10)->asForm()->post($this->url, [
                'api_key' => $this->apiKey,
                'senderid' => $this->senderId,
                'number' => ltrim(PhoneNumber::international($phone), '+'),
                'message' => $message,
            ]);

            if ($response->failed()) {
                Log::warning('SMS gateway error', ['status' => $response->status()]);
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('SMS gateway exception: '.$e->getMessage());

            return false;
        }
    }
}
