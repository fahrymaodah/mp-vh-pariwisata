<?php

declare(strict_types=1);

namespace App\Enums;

enum OooType: string
{
    case OutOfOrder = 'out_of_order';
    case OffMarket = 'off_market';
    case OutOfService = 'out_of_service';

    public function label(): string
    {
        return match ($this) {
            self::OutOfOrder => 'Out of Order',
            self::OffMarket => 'Off Market',
            self::OutOfService => 'Out of Service',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OutOfOrder => 'danger',
            self::OffMarket => 'gray',
            self::OutOfService => 'warning',
        };
    }
}
