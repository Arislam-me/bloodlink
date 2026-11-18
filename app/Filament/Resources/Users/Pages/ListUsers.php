<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'awaiting' => Tab::make('Awaiting verification')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('verified_at')->whereNotNull('phone_verified_at')),
            'suspended' => Tab::make('Suspended')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Suspended->value)),
        ];
    }
}
