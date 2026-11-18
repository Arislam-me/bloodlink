<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\DonorProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DonorProfile extends Model
{
    /** @use HasFactory<DonorProfileFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'blood_group_id', 'division_id', 'district_id', 'upazila_id', 'area_id',
        'last_donation_date', 'is_available', 'accepts_contact_requests',
        'share_phone_on_unlock', 'is_listed', 'bio', 'facebook_url', 'whatsapp_number',
        'date_of_birth', 'weight_kg',
    ];

    protected function casts(): array
    {
        return [
            'last_donation_date' => 'date',
            'date_of_birth' => 'date',
            'weight_kg' => 'integer',
            'is_available' => 'boolean',
            'accepts_contact_requests' => 'boolean',
            'share_phone_on_unlock' => 'boolean',
            'is_listed' => 'boolean',
            'available_from' => 'date',
            'auto_unavailable' => 'boolean',
        ];
    }

    /** Mirror DB defaults so new instances are complete in memory. */
    protected $attributes = [
        'auto_unavailable' => false,
    ];

    /**
     * Donation cooldown: whenever the last donation date changes — registration,
     * logging a donation, deleting one, or an admin edit — availability is
     * recalculated here, so every write path follows the same rule.
     */
    protected static function booted(): void
    {
        static::saving(function (DonorProfile $profile) {
            if ($profile->isDirty('last_donation_date')) {
                $profile->applyDonationCooldown();
            }
        });
    }

    public function applyDonationCooldown(): void
    {
        $until = $this->last_donation_date?->copy()->addMonthsNoOverflow(config('bloodlink.donation_cooldown_months'));

        if ($until && $until->isFuture()) {
            $this->is_available = false;
            $this->available_from = $until;
            $this->auto_unavailable = true;
        } elseif ($this->auto_unavailable) {
            // Cooldown no longer applies (e.g. the donation entry was removed): restore availability.
            $this->is_available = true;
            $this->available_from = null;
            $this->auto_unavailable = false;
        }
    }

    /** True while the post-donation rest period is running. */
    public function inCooldown(): bool
    {
        return $this->available_from !== null && $this->available_from->isFuture();
    }

    /** ULID only for the public identifier; the primary key stays an auto-increment integer. */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /** URLs use the non-guessable public id, never the numeric primary key. */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /* ---------------------------------------------------------------- Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(DonorProfileView::class);
    }

    /* ------------------------------------------------------------------- Scopes */

    /** Profiles that may appear publicly: listed, owner active & email-verified. */
    public function scopeSearchable(Builder $query): Builder
    {
        return $query->where('is_listed', true)
            ->whereHas('user', fn (Builder $u) => $u
                ->where('status', UserStatus::Active->value)
                ->whereNotNull('phone_verified_at'));
    }

    /**
     * Apply whitelisted search filters. Every value is cast/validated by
     * DonorSearchRequest before reaching this scope.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['blood_group'] ?? null, fn ($q, $v) => $q->where('blood_group_id', $v))
            ->when($filters['division'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['district'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['upazila'] ?? null, fn ($q, $v) => $q->where('upazila_id', $v))
            ->when($filters['area'] ?? null, fn ($q, $v) => $q->where('area_id', $v))
            ->when(($filters['availability'] ?? null) === 'available', fn ($q) => $q->where('is_available', true))
            ->when(($filters['availability'] ?? null) === 'unavailable', fn ($q) => $q->where('is_available', false))
            ->when($filters['verified'] ?? false, fn ($q) => $q->whereHas('user', fn ($u) => $u->whereNotNull('verified_at')));
    }

    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            // Longest since last donation first (never-donated treated as oldest).
            'last_donation' => $query->orderByRaw('last_donation_date IS NOT NULL, last_donation_date ASC'),
            'newest' => $query->latest('donor_profiles.created_at'),
            // Default "relevance": available first, verified first, then most recently active.
            default => $query->orderByDesc('is_available')
                ->orderByDesc(User::select('verified_at')->whereColumn('users.id', 'donor_profiles.user_id')->limit(1))
                ->orderByDesc('donor_profiles.updated_at'),
        };
    }

    /* ------------------------------------------------------------------ Helpers */

    public function generalLocation(): string
    {
        return collect([$this->upazila?->name, $this->district?->name])->filter()->implode(', ');
    }

    public function daysSinceLastDonation(): ?int
    {
        $days = $this->last_donation_date?->diffInDays(Carbon::today());

        return $days === null ? null : (int) $days;
    }

    /** Age in whole years (the birth date itself is never shown to other members). */
    public function age(): ?int
    {
        return $this->date_of_birth ? (int) $this->date_of_birth->diffInYears(Carbon::today()) : null;
    }
}
