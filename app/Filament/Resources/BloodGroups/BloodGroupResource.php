<?php

namespace App\Filament\Resources\BloodGroups;

use App\Filament\Resources\BloodGroups\Pages\ManageBloodGroups;
use App\Models\BloodGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Read-only overview: the 8 ABO/Rh groups are fixed reference data. */
class BloodGroupResource extends Resource
{
    protected static ?string $model = BloodGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?string $navigationLabel = 'Blood groups';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('code')->weight('bold'),
                TextColumn::make('donor_profiles_count')->counts('donorProfiles')->label('Registered donors'),
                TextColumn::make('available_count')->label('Available now')
                    ->state(fn (BloodGroup $r) => $r->donorProfiles()->where('is_available', true)->where('is_listed', true)->count()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBloodGroups::route('/')];
    }
}
