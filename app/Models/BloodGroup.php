<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BloodGroup extends Model
{
    public const CODES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    /**
     * Red cell compatibility: recipient group => donor groups that can give to it.
     * Used only to widen emergency notifications; final cross-matching is done by the hospital.
     */
    public const COMPATIBLE_DONORS = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-'],
    ];

    protected $fillable = ['code', 'sort_order'];

    public function donorProfiles(): HasMany
    {
        return $this->hasMany(DonorProfile::class);
    }

    /** @return array<int> ids of donor groups compatible with this recipient group */
    public function compatibleDonorGroupIds(): array
    {
        return static::query()
            ->whereIn('code', self::COMPATIBLE_DONORS[$this->code] ?? [$this->code])
            ->pluck('id')->all();
    }

    /**
     * Cached ordered list for forms, as plain objects {id, code}.
     * (Plain arrays are cached because Laravel 13 refuses to unserialize models from cache.)
     */
    public static function options(): Collection
    {
        $rows = cache()->remember('blood_groups.options', 3600,
            fn () => static::query()->orderBy('sort_order')->get(['id', 'code'])->toArray());

        return collect($rows)->map(fn (array $r) => (object) $r);
    }
}
