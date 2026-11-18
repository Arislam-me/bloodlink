<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A donor's "I can donate" response to an emergency request. */
class EmergencyRequestResponse extends Model
{
    protected $fillable = ['emergency_request_id', 'donor_id', 'message'];

    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }
}
