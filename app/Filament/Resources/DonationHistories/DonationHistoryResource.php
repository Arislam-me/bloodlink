<?php

namespace App\Filament\Resources\DonationHistories;

use App\Filament\Resources\DonationHistories\Pages\ManageDonationHistories;
use App\Models\DonationHistory;
use App\Services\DonorProfileService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Donation history is admin-controlled: donors request removal, admins approve
 * (delete, which also recalculates the donor's last donation date and cooldown)
 * or reject the request.
 */
class DonationHistoryResource extends Resource
{
    protected static ?string $model = DonationHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static ?string $navigationLabel = 'Donation history';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = DonationHistory::whereNotNull('removal_requested_at')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Removal requests awaiting review';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('removal_requested_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
            ->columns([
                TextColumn::make('user.name')->label('Donor')->searchable()
                    ->description(fn (DonationHistory $record) => $record->user?->phone),
                TextColumn::make('donated_on')->date()->sortable(),
                TextColumn::make('location')->limit(30)->placeholder('—'),
                TextColumn::make('removal_requested_at')->label('Removal requested')->since()->placeholder('—')->sortable(),
                TextColumn::make('removal_reason')->label('Reason')->limit(40)->wrap()
                    ->tooltip(fn (DonationHistory $record) => $record->removal_reason),
            ])
            ->filters([
                Filter::make('removal_requested')->label('Removal requested')
                    ->query(fn (Builder $query) => $query->whereNotNull('removal_requested_at')),
            ])
            ->recordActions([
                Action::make('approve_removal')->label('Approve & delete')->icon(Heroicon::OutlinedTrash)->color('danger')
                    ->visible(fn (DonationHistory $record) => $record->hasRemovalRequest())
                    ->requiresConfirmation()
                    ->modalDescription('The entry is deleted and the donor\'s last donation date and rest period are recalculated.')
                    ->action(function (DonationHistory $record) {
                        app(DonorProfileService::class)->deleteDonation($record);
                        Notification::make()->title('Donation entry deleted')->success()->send();
                    }),
                ActionGroup::make([
                    Action::make('reject_removal')->label('Reject request')->icon(Heroicon::OutlinedXMark)
                        ->visible(fn (DonationHistory $record) => $record->hasRemovalRequest())
                        ->requiresConfirmation()
                        ->action(fn (DonationHistory $record) => $record->forceFill(['removal_requested_at' => null, 'removal_reason' => null])->save()),
                    Action::make('delete')->label('Delete entry')->icon(Heroicon::OutlinedTrash)->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (DonationHistory $record) => app(DonorProfileService::class)->deleteDonation($record)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDonationHistories::route('/')];
    }
}
