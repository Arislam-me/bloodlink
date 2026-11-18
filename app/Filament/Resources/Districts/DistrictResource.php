<?php

namespace App\Filament\Resources\Districts;

use App\Filament\Resources\Districts\Pages\ManageDistricts;
use App\Models\District;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('division_id')->relationship('division', 'name')->required()->searchable()->preload(),
            TextInput::make('name')->required()->maxLength(60),
            TextInput::make('bn_name')->label('Bangla name')->maxLength(60),
            TextInput::make('latitude')->numeric()->minValue(20)->maxValue(27),
            TextInput::make('longitude')->numeric()->minValue(88)->maxValue(93),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('division.name')->sortable(),
                TextColumn::make('upazilas_count')->counts('upazilas')->label('Upazilas')->sortable(),
            ])
            ->filters([SelectFilter::make('division')->relationship('division', 'name')])
            ->recordActions([EditAction::make()->after(fn (District $r) => cache()->forget("loc.districts.{$r->division_id}"))]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDistricts::route('/')];
    }
}
