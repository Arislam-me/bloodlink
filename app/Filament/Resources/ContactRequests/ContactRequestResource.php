<?php

namespace App\Filament\Resources\ContactRequests;

use App\Enums\ContactRequestStatus;
use App\Filament\Resources\ContactRequests\Pages\ManageContactRequests;
use App\Models\ContactRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** Read-only oversight of contact requests (abuse investigation). */
class ContactRequestResource extends Resource
{
    protected static ?string $model = ContactRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('requester', 'donor'))
            ->columns([
                TextColumn::make('requester.name')->label('Requester')->searchable(),
                TextColumn::make('donor.name')->label('Donor')->searchable(),
                TextColumn::make('message')->limit(50)->tooltip(fn (ContactRequest $r) => $r->message)->wrap(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
                TextColumn::make('responded_at')->since()->placeholder('—'),
            ])
            ->filters([SelectFilter::make('status')->options(ContactRequestStatus::class)]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactRequests::route('/')];
    }
}
