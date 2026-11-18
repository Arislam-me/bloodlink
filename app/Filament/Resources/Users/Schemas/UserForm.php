<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Account')->columnSpan(2)->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->required()->regex('/^01[3-9]\d{8}$/')->unique(ignoreRecord: true),
                Select::make('role')->options(UserRole::class)->required()
                    // An admin cannot remove their own admin role (prevents lock-out).
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                Select::make('status')->options(UserStatus::class)->required()->live()
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                TextInput::make('status_reason')->maxLength(255)->helperText('Shown internally; explain suspensions.'),
            ]),
            Section::make('Verification')->columnSpan(1)->schema([
                DateTimePicker::make('email_verified_at')->label('Email verified'),
                DateTimePicker::make('phone_verified_at')->label('Mobile verified'),
                DateTimePicker::make('verified_at')->label('Donor identity verified')
                    ->helperText('Set to show the "Verified donor" badge.'),
                TextEntry::make('created_at')->label('Registered')->dateTime(),
                TextEntry::make('last_login_at')->label('Last login')->dateTime()->placeholder('Never'),
            ]),
        ]);
    }
}
