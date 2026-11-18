<?php

namespace App\Services;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Services\Sms\SmsSender;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * One-time-password verification for mobile numbers.
 * Codes are stored hashed, expire, are single-use and allow limited attempts.
 */
class PhoneVerificationService
{
    public function __construct(private readonly SmsSender $sms) {}

    /** @return int seconds the user must wait before requesting another code (0 = sent now) */
    public function send(User $user): int
    {
        $cfg = config('bloodlink.otp');

        $latest = PhoneVerificationCode::where('user_id', $user->id)->latest('id')->first();
        if ($latest && $latest->created_at->diffInSeconds(now()) < $cfg['resend_cooldown_seconds']) {
            return (int) ($cfg['resend_cooldown_seconds'] - $latest->created_at->diffInSeconds(now()));
        }

        // Invalidate older unused codes.
        PhoneVerificationCode::where('user_id', $user->id)->whereNull('used_at')->update(['used_at' => now()]);

        $code = str_pad((string) random_int(0, 10 ** $cfg['length'] - 1), $cfg['length'], '0', STR_PAD_LEFT);

        PhoneVerificationCode::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($cfg['ttl_minutes']),
        ]);

        $this->sms->send($user->phone, "BloodLink যাচাই কোড: {$code}। কোডটি {$cfg['ttl_minutes']} মিনিটের মধ্যে ব্যবহার করুন। কাউকে এই কোড জানাবেন না।");

        return 0;
    }

    /** @throws ValidationException */
    public function verify(User $user, string $code): void
    {
        $record = PhoneVerificationCode::where('user_id', $user->id)
            ->where('phone', $user->phone)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        $fail = fn (string $msg) => throw ValidationException::withMessages(['code' => $msg]);

        if (! $record || $record->expires_at->isPast()) {
            $fail('কোডটির মেয়াদ শেষ। নতুন কোড চেয়ে নিন।');
        }

        if ($record->attempts >= config('bloodlink.otp.max_attempts')) {
            $record->forceFill(['used_at' => now()])->save();
            $fail('অনেকবার ভুল কোড দেওয়া হয়েছে। নতুন কোড চেয়ে নিন।');
        }

        if (! Hash::check($code, $record->code_hash)) {
            $record->increment('attempts');
            $fail('কোডটি সঠিক নয়। আবার চেষ্টা করুন।');
        }

        $record->forceFill(['used_at' => now()])->save();
        $user->forceFill(['phone_verified_at' => now()])->save();
    }
}
