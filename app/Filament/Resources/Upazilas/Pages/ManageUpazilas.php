<?php

namespace App\Filament\Resources\Upazilas\Pages;

use App\Filament\Resources\Upazilas\UpazilaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUpazilas extends ManageRecords
{
    protected static string $resource = UpazilaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
