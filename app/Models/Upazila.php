<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upazila extends Model
{
    protected $fillable = ['district_id', 'name', 'bn_name', 'is_city_thana', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['is_city_thana' => 'boolean', 'latitude' => 'float', 'longitude' => 'float'];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class)->orderBy('name');
    }
}
