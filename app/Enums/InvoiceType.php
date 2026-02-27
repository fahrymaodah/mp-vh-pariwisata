<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceType: string
{
    case Guest = 'guest';
    case NonStayGuest = 'non_stay_guest';
    case MasterBill = 'master_bill';

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Guest',
            self::NonStayGuest => 'Non-Stay Guest',
            self::MasterBill => 'Master Bill',
        };
    }
}
