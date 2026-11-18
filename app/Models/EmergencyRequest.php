<?php

namespace App\Models;

use App\Enums\ContactMethod;
use App\Enums\EmergencyStatus;
use Database\Factories\EmergencyRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EmergencyRequest extends Model
{
    /** @use HasFactory<EmergencyRequestFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'blood_group_id', 'patient_condition', 'hospital_name', 'hospital_address',
        'division_id', 'district_id', 'upazila_id', 'latitude', 'longitude',
        'units_needed', 'needed_at', 'contact_method', 'contact_phone', 'details',
    ];

    protected function casts(): array
    {
        return [
            'status' => EmergencyStatus::class,
            'contact_method' => ContactMethod::class,
            'needed_at' => 'datetime',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'donors_notified_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'units_needed' => 'integer',
            'units_fulfilled' => 'integer',
        ];
    }

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EmergencyRequestResponse::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /* ------------------------------------------------------------------- Scopes */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmergencyStatus::Active->value)->where('expires_at', '>', now());
    }

    /** @param array<string, mixed> $filters */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['blood_group'] ?? null, fn ($q, $v) => $q->where('blood_group_id', $v))
            ->when($filters['division'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['district'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['upazila'] ?? null, fn ($q, $v) => $q->where('upazila_id', $v));
    }

    /* ------------------------------------------------------------------ Helpers */

    public function isOpen(): bool
    {
        return $this->status->isOpen() && $this->expires_at->isFuture();
    }

    public function isUrgent(): bool
    {
        return $this->needed_at->lessThanOrEqualTo(now()->addHours(24));
    }

    public function generalLocation(): string
    {
        return collect([$this->upazila?->name, $this->district?->name])->filter()->implode(', ');
    }

    public function osmUrl(): ?string
    {
        if (! $this->latitude || ! $this->longitude) {
            return null;
        }

        return sprintf('https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=16/%1$s/%2$s', $this->latitude, $this->longitude);
    }
}
