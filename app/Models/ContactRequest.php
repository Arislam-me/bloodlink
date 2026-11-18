<?php

namespace App\Models;

use App\Enums\ContactRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequest extends Model
{
    protected $fillable = ['requester_id', 'donor_id', 'emergency_request_id', 'message', 'expires_at'];

    protected function casts(): array
    {
        return [
            'status' => ContactRequestStatus::class,
            'responded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class);
    }

    public function isPending(): bool
    {
        return $this->status === ContactRequestStatus::Pending;
    }

    /** Phone numbers are exchanged only after the donor explicitly accepts. */
    public function sharesContactDetails(): bool
    {
        return $this->status === ContactRequestStatus::Accepted;
    }
}
