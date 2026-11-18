<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Append-only audit log of consent grants and withdrawals. */
class UserConsent extends Model
{
    public const TERMS = 'terms';

    public const PRIVACY = 'privacy';

    public const CONTACT_SHARING = 'contact_sharing';

    public const PHONE_ON_UNLOCK = 'phone_on_unlock';

    protected $fillable = ['consent_type', 'version', 'granted', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['granted' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(User $user, string $type, bool $granted, ?Request $request = null): self
    {
        $request ??= request();

        return $user->consents()->create([
            'consent_type' => $type,
            'version' => config('bloodlink.legal_version'),
            'granted' => $granted,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 250, ''),
        ]);
    }
}
