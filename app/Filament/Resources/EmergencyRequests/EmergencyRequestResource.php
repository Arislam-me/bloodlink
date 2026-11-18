<?php

namespace App\Filament\Resources\EmergencyRequests;

use App\Enums\EmergencyStatus;
use App\Filament\Resources\EmergencyRequests\Pages\EditEmergencyRequest;
use App\Filament\Resources\EmergencyRequests\Pages\ListEmergencyRequests;
use App\Filament\Resources\EmergencyRequests\Schemas\EmergencyRequestForm;
use App\Filament\Resources\EmergencyRequests\Tables\EmergencyRequestsTable;
use App\Models\EmergencyRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EmergencyRequestResource extends Resource
{
    protected static ?string $model = EmergencyRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'hospital_name';

    public static function getNavigationBadge(): ?string
    {
        $n = EmergencyRequest::where('status', EmergencyStatus::Pending->value)->where('expires_at', '>', now())->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending review';
    }

    public static function form(Schema $schema): Schema
    {
        return EmergencyRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmergencyRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user', 'bloodGroup', 'district', 'upazila')->withCount('responses', 'reports');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmergencyRequests::route('/'),
            'edit' => EditEmergencyRequest::route('/{record}/edit'),
        ];
    }
}
