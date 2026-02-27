<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Open = 'open';
    case Printed = 'printed';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Printed => 'Printed',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Printed => 'info',
            self::Closed => 'success',
            self::Reopened => 'danger',
        };
    }
}
