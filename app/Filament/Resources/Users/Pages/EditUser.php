<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\ForceFillsRecord;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use ForceFillsRecord;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Soft delete (recoverable). Hard delete is used for processed data deletion requests.
            DeleteAction::make()->hidden(fn () => $this->record->is(auth()->user())),
            RestoreAction::make(),
            ForceDeleteAction::make()->hidden(fn () => $this->record->is(auth()->user())),
        ];
    }
}
