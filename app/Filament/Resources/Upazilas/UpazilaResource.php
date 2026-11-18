<?php

namespace App\Filament\Resources\Upazilas;

use App\Filament\Resources\Upazilas\Pages\ManageUpazilas;
use App\Models\DonorProfile;
use App\Models\Upazila;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UpazilaResource extends Resource
{
    protected static ?string $model = Upazila::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?string $navigationLabel = 'Upazilas & Thanas';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('district_id')->relationship('district', 'name')->required()->searchable()->preload(),
            TextInput::make('name')->required()->maxLength(80),
            TextInput::make('bn_name')->label('Bangla name')->maxLength(80),
            Toggle::make('is_city_thana')->label('Metropolitan city thana'),
            TextInput::make('latitude')->numeric()->minValue(20)->maxValue(27),
            TextInput::make('longitude')->numeric()->minValue(88)->maxValue(93),
        ]);
    }

    public static function table(Table $table): Table
    {
        $forget = fn (Upazila $r) => cache()->forget("loc.upazilas.{$r->district_id}");

        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('district.name')->searchable()->sortable(),
                TextColumn::make('district.division.name')->label('Division'),
                IconColumn::make('is_city_thana')->boolean()->label('City'),
                TextColumn::make('areas_count')->counts('areas')->label('Areas'),
            ])
            ->filters([
                SelectFilter::make('district')->relationship('district', 'name')->searchable()->preload(),
                TernaryFilter::make('is_city_thana')->label('City thana'),
            ])
            ->recordActions([
                EditAction::make()->after($forget),
                // Only deletable when no donors/requests reference it (FK restrictOnDelete also enforces this).
                DeleteAction::make()->after($forget)
                    ->hidden(fn (Model $r) => DonorProfile::where('upazila_id', $r->id)->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUpazilas::route('/')];
    }
}
