<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin edit pages may change guarded columns (role, status, verification).
 * The public site relies on $fillable for mass-assignment protection, so admin
 * forms — whose fields are explicitly declared in the schema — use forceFill.
 */
trait ForceFillsRecord
{
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->forceFill($data)->save();

        return $record;
    }
}
