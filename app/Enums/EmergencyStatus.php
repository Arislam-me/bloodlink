<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmergencyStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';     // awaiting admin review (unverified requester)
    case Active = 'active';       // visible publicly, donors notified
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'যাচাই চলছে',
            self::Active => 'চলমান',
            self::Fulfilled => 'পূরণ হয়েছে',
            self::Cancelled => 'বাতিল',
            self::Expired => 'মেয়াদ শেষ',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'danger',
            self::Fulfilled => 'success',
            self::Cancelled, self::Expired => 'gray',
        };
    }

    /** Tailwind classes for the public site badge. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::Active => 'bg-red-50 text-red-800 ring-red-200',
            self::Fulfilled => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Cancelled, self::Expired => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Active], true);
    }
}
