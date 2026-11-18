<?php

namespace App\Filament\Resources\DataDeletionRequests;

use App\Filament\Resources\DataDeletionRequests\Pages\ManageDataDeletionRequests;
use App\Models\DataDeletionRequest;
use App\Services\DataDeletionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DataDeletionRequestResource extends Resource
{
    protected static ?string $model = DataDeletionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & Safety';

    protected static ?string $navigationLabel = 'Data deletion requests';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $n = DataDeletionRequest::where('status', 'pending')->count();

        return $n ? (string) $n : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user', 'processor'))
            ->columns([
                TextColumn::make('email')->searchable(),
                TextColumn::make('user.name')->label('User')->placeholder('(deleted)'),
                TextColumn::make('type')->badge()->formatStateUsing(fn ($state) => $state === 'account' ? 'Entire account' : 'Personal data'),
                TextColumn::make('reason')->limit(40)->tooltip(fn ($record) => $record->reason),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'warning', 'completed' => 'success', default => 'gray'
                }),
                TextColumn::make('created_at')->label('Requested')->since()->sortable(),
                TextColumn::make('processor.name')->label('Processed by')->placeholder('—'),
            ])
            ->filters([SelectFilter::make('status')->options(['pending' => 'Pending', 'completed' => 'Completed', 'rejected' => 'Rejected'])])
            ->recordActions([
                Action::make('process')->label('Process deletion')->icon(Heroicon::OutlinedTrash)->color('danger')
                    ->visible(fn (DataDeletionRequest $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently delete data?')
                    ->modalDescription(fn (DataDeletionRequest $r) => $r->type === 'account'
                        ? 'The account and all related records will be permanently deleted. This cannot be undone.'
                        : 'Donor profile, history and contact data will be deleted and the account anonymized. This cannot be undone.')
                    ->action(function (DataDeletionRequest $r) {
                        app(DataDeletionService::class)->process($r, auth()->user());
                        Notification::make()->title('Deletion completed')->success()->send();
                    }),
                Action::make('reject')->icon(Heroicon::OutlinedXMark)
                    ->visible(fn (DataDeletionRequest $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (DataDeletionRequest $r) => $r->forceFill(['status' => 'rejected', 'processed_by' => auth()->id(), 'processed_at' => now()])->save()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDataDeletionRequests::route('/')];
    }
}
