<?php

namespace App\Filament\Resources\DonorProfileViews\Pages;

use App\Filament\Resources\DonorProfileViews\DonorProfileViewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDonorProfileViews extends ManageRecords
{
    protected static string $resource = DonorProfileViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
