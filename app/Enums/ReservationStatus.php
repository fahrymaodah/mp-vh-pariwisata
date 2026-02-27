<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationStatus: string
{
    case Guaranteed = 'guaranteed';
    case SixPm = 'six_pm';
    case OralConfirmed = 'oral_confirmed';
    case Tentative = 'tentative';
    case WaitingList = 'waiting_list';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';

    public function label(): string
    {
        return match ($this) {
            self::Guaranteed => 'Guaranteed',
            self::SixPm => '6 PM Release',
            self::OralConfirmed => 'Oral Confirmed',
            self::Tentative => 'Tentative',
            self::WaitingList => 'Waiting List',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No Show',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Guaranteed => 'success',
            self::SixPm => 'warning',
            self::OralConfirmed => 'info',
            self::Tentative => 'gray',
            self::WaitingList => 'orange',
            self::Confirmed => 'primary',
            self::Cancelled => 'danger',
            self::NoShow => 'danger',
            self::CheckedIn => 'success',
            self::CheckedOut => 'gray',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::Guaranteed,
            self::SixPm,
            self::OralConfirmed,
            self::Tentative,
            self::WaitingList,
            self::Confirmed,
            self::CheckedIn,
        ]);
    }
}
