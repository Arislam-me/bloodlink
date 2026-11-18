<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Accounts are verified by mobile OTP only (no email verification).
 */
class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Only profile fields are mass-assignable. Role, status, and verification
     * columns are set explicitly by trusted code paths (admin actions, services).
     */
    protected $fillable = ['name', 'email', 'phone', 'password', 'profile_photo_path'];

    protected $hidden = ['password', 'remember_token'];

    /** Mirror DB defaults so freshly created instances are complete in memory. */
    protected $attributes = [
        'role' => 'user',
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    /* ---------------------------------------------------------------- Relations */

    public function donorProfile(): HasOne
    {
        return $this->hasOne(DonorProfile::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(DonationHistory::class)->latest('donated_on');
    }

    public function profileViews(): HasMany
    {
        return $this->hasMany(DonorProfileView::class, 'viewer_id');
    }

    public function sentContactRequests(): HasMany
    {
        return $this->hasMany(ContactRequest::class, 'requester_id');
    }

    public function receivedContactRequests(): HasMany
    {
        return $this->hasMany(ContactRequest::class, 'donor_id');
    }

    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    public function reportsFiled(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsAgainst(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /* ----------------------------------------------------------------- Helpers */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isVerifiedDonor(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /** Fully trusted for privacy-sensitive actions (unlocking contacts, requests). */
    public function isFullyVerified(): bool
    {
        return $this->hasVerifiedPhone() && $this->isActive();
    }

    /** Privacy-safe display name for public listings, e.g. "Rahim K." */
    public function publicName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [$this->name];
        $first = $parts[0];

        return count($parts) > 1
            ? $first.' '.Str::upper(Str::substr(end($parts), 0, 1)).'.'
            : $first;
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)
            ->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))->implode('');
    }

    public function profilePhotoUrl(): ?string
    {
        // Raw attribute lookup: safe on partially-selected models under strict mode.
        $path = $this->attributes['profile_photo_path'] ?? null;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /** Masked phone for the owner's own UI, e.g. 017•••••678 */
    public function maskedPhone(): string
    {
        return Str::substr($this->phone, 0, 3).'•••••'.Str::substr($this->phone, -3);
    }

    /* ------------------------------------------------------------------ Filament */

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->isActive();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
