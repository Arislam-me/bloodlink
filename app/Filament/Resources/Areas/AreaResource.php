<?php

namespace App\Filament\Resources\Areas;

use App\Filament\Resources\Areas\Pages\ManageAreas;
use App\Models\Area;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?string $navigationLabel = 'Areas / Neighborhoods';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $n = Area::where('is_approved', false)->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'User-submitted areas awaiting review';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('upazila_id')->relationship('upazila', 'name')->required()->searchable(),
            TextInput::make('name')->required()->maxLength(100),
            Toggle::make('is_approved')->default(true)->helperText('Approved areas appear in dropdowns.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_approved')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('upazila.name')->searchable(),
                TextColumn::make('upazila.district.name')->label('District'),
                IconColumn::make('is_approved')->boolean()->label('Approved'),
                TextColumn::make('donor_profiles_count')->counts('donorProfiles')->label('Donors'),
            ])
            ->filters([TernaryFilter::make('is_approved')->label('Approved')])
            ->recordActions([
                Action::make('approve')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (Area $r) => ! $r->is_approved)
                    ->action(fn (Area $r) => $r->update(['is_approved' => true])),
                Action::make('merge')->label('Merge into…')->icon(Heroicon::OutlinedArrowsPointingIn)
                    ->schema(fn (Area $r) => [
                        Select::make('target_id')->label('Merge into area')->required()
                            ->options(Area::where('upazila_id', $r->upazila_id)->whereKeyNot($r->id)->pluck('name', 'id')),
                    ])
                    ->action(function (Area $r, array $data) {
                        // Re-point donors to the canonical area, then remove the duplicate.
                        DB::transaction(function () use ($r, $data) {
                            $r->donorProfiles()->update(['area_id' => $data['target_id']]);
                            $r->delete();
                        });
                        Notification::make()->title('Areas merged')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => $records->each->update(['is_approved' => true])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAreas::route('/')];
    }
}
