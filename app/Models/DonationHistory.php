<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationHistory extends Model
{
    use HasFactory;

    protected $fillable = ['donated_on', 'location', 'notes', 'emergency_request_id'];

    protected function casts(): array
    {
        return ['donated_on' => 'date', 'verified_at' => 'datetime', 'removal_requested_at' => 'datetime'];
    }

    public function hasRemovalRequest(): bool
    {
        return ($this->attributes['removal_requested_at'] ?? null) !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
