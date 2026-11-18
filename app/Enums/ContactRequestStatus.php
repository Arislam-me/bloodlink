<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactRequestStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Cancelled, self::Expired => 'gray',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::Accepted => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Declined => 'bg-red-50 text-red-800 ring-red-200',
            self::Cancelled, self::Expired => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }
}
