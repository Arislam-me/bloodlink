<?php

namespace App\Filament\Resources\DonorProfiles\Schemas;

use App\Models\Area;
use App\Models\Upazila;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DonorProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Donor')->columnSpan(2)->columns(2)->schema([
                TextEntry::make('user.name')->label('User'),
                TextEntry::make('user.email')->label('Email'),
                Select::make('blood_group_id')->relationship('bloodGroup', 'code')->required(),
                DatePicker::make('last_donation_date')->maxDate(now()),
                DatePicker::make('date_of_birth')->label('Date of birth')->maxDate(now()),
                TextInput::make('weight_kg')->label('Weight (kg)')->numeric()->minValue(20)->maxValue(250),
                Select::make('upazila_id')->label('Upazila / Thana')->required()->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Upazila::with('district')->where('name', 'like', "%{$search}%")->limit(30)->get()
                        ->mapWithKeys(fn ($u) => [$u->id => "{$u->name}, {$u->district->name}"]))
                    ->getOptionLabelUsing(fn ($value) => ($u = Upazila::with('district')->find($value)) ? "{$u->name}, {$u->district->name}" : null)
                    ->live()
                    // Keep the hierarchy consistent: district/division derived from the upazila.
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if ($u = Upazila::with('district')->find($state)) {
                            $set('district_id', $u->district_id);
                            $set('division_id', $u->district->division_id);
                            $set('area_id', null);
                        }
                    }),
                Select::make('area_id')->label('Area')->nullable()
                    ->options(fn (Get $get) => Area::where('upazila_id', $get('upazila_id'))->pluck('name', 'id')),
                Select::make('district_id')->relationship('district', 'name')->disabled()->dehydrated(),
                Select::make('division_id')->relationship('division', 'name')->disabled()->dehydrated(),
                TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255),
                TextInput::make('whatsapp_number')->label('WhatsApp')->regex('/^01[3-9]\d{8}$/'),
                Textarea::make('bio')->maxLength(500)->columnSpanFull(),
            ]),
            Section::make('Visibility & consent')->columnSpan(1)->schema([
                Toggle::make('is_available')->label('Available'),
                Toggle::make('accepts_contact_requests'),
                Toggle::make('is_listed')->label('Listed in search'),
                Toggle::make('share_phone_on_unlock')->label('Phone shared on unlock')
                    ->disabled()->helperText('Only the donor can change their consent.'),
            ]),
        ]);
    }
}
