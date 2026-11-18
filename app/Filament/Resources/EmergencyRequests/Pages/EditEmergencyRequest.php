<?php

namespace App\Filament\Resources\EmergencyRequests\Pages;

use App\Filament\Concerns\ForceFillsRecord;
use App\Filament\Resources\EmergencyRequests\EmergencyRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmergencyRequest extends EditRecord
{
    use ForceFillsRecord;

    protected static string $resource = EmergencyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
