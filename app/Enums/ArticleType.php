<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleType: string
{
    case Sales = 'sales';
    case Payment = 'payment';

    public function label(): string
    {
        return match ($this) {
            self::Sales => 'Sales',
            self::Payment => 'Payment',
        };
    }
}
