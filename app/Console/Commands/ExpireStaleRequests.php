<?php

namespace App\Console\Commands;

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest;
use App\Models\PhoneVerificationCode;
use App\Services\EmergencyRequestService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bloodlink:expire')]
#[Description('Expire overdue emergency & contact requests and prune old OTP codes')]
class ExpireStaleRequests extends Command
{
    public function handle(EmergencyRequestService $emergency): int
    {
        $e = $emergency->expireOverdue();

        $c = ContactRequest::where('status', ContactRequestStatus::Pending->value)
            ->where('expires_at', '<=', now())
            ->update(['status' => ContactRequestStatus::Expired->value, 'updated_at' => now()]);

        $o = PhoneVerificationCode::where('created_at', '<', now()->subDays(7))->delete();

        $this->info("Expired {$e} emergency request(s), {$c} contact request(s); pruned {$o} OTP code(s).");

        return self::SUCCESS;
    }
}
