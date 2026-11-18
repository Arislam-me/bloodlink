<?php

namespace App\Filament\Resources\DonorProfiles;

use App\Filament\Resources\DonorProfiles\Pages\EditDonorProfile;
use App\Filament\Resources\DonorProfiles\Pages\ListDonorProfiles;
use App\Filament\Resources\DonorProfiles\Schemas\DonorProfileForm;
use App\Filament\Resources\DonorProfiles\Tables\DonorProfilesTable;
use App\Models\DonorProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DonorProfileResource extends Resource
{
    protected static ?string $model = DonorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static ?string $navigationLabel = 'Donors';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DonorProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonorProfilesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user', 'bloodGroup', 'district', 'upazila', 'division');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonorProfiles::route('/'),
            'edit' => EditDonorProfile::route('/{record}/edit'),
        ];
    }
}
