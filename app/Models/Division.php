<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Division extends Model
{
    protected $fillable = ['name', 'bn_name', 'latitude', 'longitude'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class)->orderBy('name');
    }

    public static function options(): Collection
    {
        $rows = cache()->remember('divisions.options', 3600,
            fn () => static::query()->orderBy('name')->get(['id', 'name'])->toArray());

        return collect($rows)->map(fn (array $r) => (object) $r);
    }
}
