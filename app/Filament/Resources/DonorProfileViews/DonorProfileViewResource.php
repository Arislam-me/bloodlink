<?php

namespace App\Filament\Resources\DonorProfileViews;

use App\Filament\Resources\DonorProfileViews\Pages\ManageDonorProfileViews;
use App\Models\DonorProfileView;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Read-only audit log of donor contact-profile unlocks (monthly limit monitoring).
 * Use the "Top viewers" dashboard widget to spot accounts hitting the limit.
 */
class DonorProfileViewResource extends Resource
{
    protected static ?string $model = DonorProfileView::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & Safety';

    protected static ?string $navigationLabel = 'Profile view log';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('viewer', 'donorProfile.user', 'donorProfile.bloodGroup'))
            ->columns([
                TextColumn::make('viewer.name')->label('Viewer')->searchable()
                    ->description(fn (DonorProfileView $r) => $r->viewer?->email),
                TextColumn::make('donorProfile.user.name')->label('Donor unlocked')->searchable(),
                TextColumn::make('donorProfile.bloodGroup.code')->label('Blood')->badge()->color('danger'),
                TextColumn::make('period')->label('Month')->sortable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('period')->label('Month')
                    ->options(fn () => DonorProfileView::query()->distinct()->orderByDesc('period')->limit(12)->pluck('period', 'period')),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDonorProfileViews::route('/')];
    }
}
