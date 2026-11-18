<?php

namespace App\Filament\Resources\Divisions;

use App\Filament\Resources\Divisions\Pages\ManageDivisions;
use App\Models\Division;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(60)->unique(ignoreRecord: true),
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
                TextColumn::make('bn_name')->label('Bangla'),
                TextColumn::make('districts_count')->counts('districts')->label('Districts')->sortable(),
            ])
            // Divisions are fixed administrative units: edit only, no delete.
            ->recordActions([EditAction::make()->after(fn () => cache()->forget('divisions.options'))]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDivisions::route('/')];
    }
}
