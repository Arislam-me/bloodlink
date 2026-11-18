<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row = one unique donor contact profile unlocked by a viewer in a calendar month.
 * Rows older than 12 months are pruned daily (`model:prune`), per the privacy policy.
 */
class DonorProfileView extends Model
{
    use MassPrunable;

    protected $fillable = ['viewer_id', 'donor_profile_id', 'period', 'ip_address', 'user_agent'];

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(12));
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function donorProfile(): BelongsTo
    {
        return $this->belongsTo(DonorProfile::class);
    }
}
