<?php

namespace App\Filament\Resources\EmergencyRequests\Pages;

use App\Enums\EmergencyStatus;
use App\Filament\Resources\EmergencyRequests\EmergencyRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmergencyRequests extends ListRecords
{
    protected static string $resource = EmergencyRequestResource::class;

    /** "All" comes first so no status filter is applied by default. */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', EmergencyStatus::Active->value)),
            'pending' => Tab::make('Pending review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', EmergencyStatus::Pending->value)),
            'reported' => Tab::make('Reported')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('reports')),
        ];
    }
}
