<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReportReason: string implements HasLabel
{
    case FakeRequest = 'fake_request';
    case Harassment = 'harassment';
    case Spam = 'spam';
    case Scam = 'scam';               // e.g. asking for money for blood
    case Inappropriate = 'inappropriate';
    case FalseInformation = 'false_information';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::FakeRequest => 'ভুয়া বা বিভ্রান্তিকর অনুরোধ',
            self::Harassment => 'হয়রানি বা খারাপ ব্যবহার',
            self::Spam => 'স্প্যাম',
            self::Scam => 'প্রতারণা / টাকা চাওয়া',
            self::Inappropriate => 'অশোভন কিছু',
            self::FalseInformation => 'প্রোফাইলে ভুল তথ্য',
            self::Other => 'অন্য কিছু',
        };
    }
}
