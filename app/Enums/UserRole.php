<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case User = 'user';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::User => 'Donor / User',
            self::Admin => 'Administrator',
        };
    }

    public function getColor(): string
    {
        return $this === self::Admin ? 'danger' : 'gray';
    }
}
