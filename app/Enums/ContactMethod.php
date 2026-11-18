<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactMethod: string implements HasLabel
{
    case InApp = 'in_app';  // donors respond through BloodLink; requester phone stays private
    case Phone = 'phone';   // requester chooses to publish a hospital/attendant phone to signed-in donors

    public function getLabel(): string
    {
        return match ($this) {
            self::InApp => 'In-app responses only',
            self::Phone => 'Phone (shown to signed-in, verified donors)',
        };
    }
}
