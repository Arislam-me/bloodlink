<?php

namespace App\Filament\Resources\EmergencyRequests\Schemas;

use App\Enums\ContactMethod;
use App\Enums\EmergencyStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmergencyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Request')->columnSpan(2)->columns(2)->schema([
                Select::make('blood_group_id')->relationship('bloodGroup', 'code')->required(),
                TextInput::make('units_needed')->numeric()->minValue(1)->maxValue(10)->required(),
                TextInput::make('hospital_name')->required()->maxLength(150),
                TextInput::make('hospital_address')->required()->maxLength(255),
                TextInput::make('patient_condition')->maxLength(150),
                DateTimePicker::make('needed_at')->required(),
                Select::make('contact_method')->options(ContactMethod::class)->required(),
                TextInput::make('contact_phone')->regex('/^01[3-9]\d{8}$/'),
                Textarea::make('details')->maxLength(2000)->columnSpanFull(),
            ]),
            Section::make('Moderation')->columnSpan(1)->schema([
                Select::make('status')->options(EmergencyStatus::class)->required(),
                DateTimePicker::make('expires_at')->required(),
                TextEntry::make('user.name')->label('Requested by'),
                TextEntry::make('user.phone')->label('Requester mobile'),
                TextEntry::make('notified_count')->label('Donors notified'),
                TextEntry::make('approved_at')->dateTime()->placeholder('Not approved'),
                TextEntry::make('created_at')->dateTime(),
            ]),
        ]);
    }
}
