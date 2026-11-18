<?php

namespace App\Filament\Resources\DonorProfiles\Pages;

use App\Filament\Resources\DonorProfiles\DonorProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListDonorProfiles extends ListRecords
{
    protected static string $resource = DonorProfileResource::class;
}
