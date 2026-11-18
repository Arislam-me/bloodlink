<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataDeletionRequest extends Model
{
    protected $fillable = ['user_id', 'email', 'type', 'reason'];

    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
