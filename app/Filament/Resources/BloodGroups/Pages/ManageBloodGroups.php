<?php

namespace App\Filament\Resources\BloodGroups\Pages;

use App\Filament\Resources\BloodGroups\BloodGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBloodGroups extends ManageRecords
{
    protected static string $resource = BloodGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
