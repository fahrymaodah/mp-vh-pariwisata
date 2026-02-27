<?php

declare(strict_types=1);

namespace App\Enums;

enum RoomStatus: string
{
    case VacantClean = 'vacant_clean';
    case VacantCleanUnchecked = 'vacant_clean_unchecked';
    case VacantDirty = 'vacant_dirty';
    case ExpectedDeparture = 'expected_departure';
    case OccupiedDirty = 'occupied_dirty';
    case OccupiedClean = 'occupied_clean';
    case OutOfOrder = 'out_of_order';
    case DoNotDisturb = 'do_not_disturb';

    public function label(): string
    {
        return match ($this) {
            self::VacantClean => 'Vacant Clean',
            self::VacantCleanUnchecked => 'Vacant Clean Unchecked',
            self::VacantDirty => 'Vacant Dirty',
            self::ExpectedDeparture => 'Expected Departure',
            self::OccupiedDirty => 'Occupied Dirty',
            self::OccupiedClean => 'Occupied Clean',
            self::OutOfOrder => 'Out of Order',
            self::DoNotDisturb => 'Do Not Disturb',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VacantClean => 'success',
            self::VacantCleanUnchecked => 'lime',
            self::VacantDirty => 'warning',
            self::ExpectedDeparture => 'orange',
            self::OccupiedDirty => 'info',
            self::OccupiedClean => 'primary',
            self::OutOfOrder => 'danger',
            self::DoNotDisturb => 'gray',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this, [self::VacantClean, self::VacantDirty, self::VacantCleanUnchecked]);
    }

    public function isOccupied(): bool
    {
        return in_array($this, [self::OccupiedClean, self::OccupiedDirty, self::ExpectedDeparture]);
    }
}
