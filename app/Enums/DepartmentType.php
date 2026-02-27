<?php

declare(strict_types=1);

namespace App\Enums;

enum DepartmentType: string
{
    case Hotel = 'hotel';
    case Restaurant = 'restaurant';
    case Drugstore = 'drugstore';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Hotel => 'Hotel',
            self::Restaurant => 'Restaurant',
            self::Drugstore => 'Drugstore',
            self::Other => 'Other',
        };
    }
}
