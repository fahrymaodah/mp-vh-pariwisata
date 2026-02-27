<?php

declare(strict_types=1);

namespace App\Enums;

enum GuestType: string
{
    case Individual = 'individual';
    case Company = 'company';
    case TravelAgent = 'travel_agent';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Company => 'Company',
            self::TravelAgent => 'Travel Agent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Individual => 'primary',
            self::Company => 'success',
            self::TravelAgent => 'warning',
        };
    }
}
