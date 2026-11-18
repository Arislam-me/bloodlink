<?php

namespace App\Filament\Resources\DataDeletionRequests\Pages;

use App\Filament\Resources\DataDeletionRequests\DataDeletionRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDataDeletionRequests extends ManageRecords
{
    protected static string $resource = DataDeletionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
