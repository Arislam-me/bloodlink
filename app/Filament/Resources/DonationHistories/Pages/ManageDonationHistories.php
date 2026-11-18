<?php

namespace App\Filament\Resources\DonationHistories\Pages;

use App\Filament\Resources\DonationHistories\DonationHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDonationHistories extends ManageRecords
{
    protected static string $resource = DonationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
