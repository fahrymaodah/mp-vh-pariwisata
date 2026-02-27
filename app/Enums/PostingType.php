<?php

declare(strict_types=1);

namespace App\Enums;

enum PostingType: string
{
    case Daily = 'daily';
    case CheckedIn = 'checked_in';
    case FirstDay = 'first_day';
    case LastDay = 'last_day';
    case Special = 'special';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::CheckedIn => 'On Check-In',
            self::FirstDay => 'First Day',
            self::LastDay => 'Last Day',
            self::Special => 'Special',
        };
    }
}
