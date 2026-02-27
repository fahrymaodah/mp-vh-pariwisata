<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\OooType;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomOutOfOrder;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RoomStatusOverview extends Page
{
    protected string $view = 'filament.hk.pages.room-status-overview';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BuildingOffice2;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Room Status Overview';

    protected static ?string $title = 'Room Status Overview';

    protected static ?string $slug = 'room-status-overview';

    public function getArrivalDeparture(): array
    {
        $today = SystemDate::today();

        $departureToday = \App\Models\Reservation::where('departure_date', $today)
            ->where('status', ReservationStatus::CheckedIn)
            ->count();

        $departed = \App\Models\Reservation::where('departure_date', $today)
            ->where('status', ReservationStatus::CheckedOut)
            ->count();

        $checkedInToday = \App\Models\Reservation::whereDate('check_in_time', $today)
            ->whereIn('status', [ReservationStatus::CheckedIn, ReservationStatus::CheckedOut])
            ->count();

        $arriving = \App\Models\Reservation::where('arrival_date', $today)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::Guaranteed])
            ->count();

        return [
            'departure_today' => $departureToday,
            'departed' => $departed,
            'total_departure' => $departureToday + $departed,
            'checked_in_today' => $checkedInToday,
            'arriving' => $arriving,
            'total_arrival' => $checkedInToday + $arriving,
        ];
    }

    public function getRoomOccupancy(): array
    {
        $today = SystemDate::today();
        $totalRooms = Room::active()->count();

        $occupied = Room::active()->occupied()->count();
        $oooCount = RoomOutOfOrder::where('type', OooType::OutOfOrder)
            ->where('from_date', '<=', $today)
            ->where('until_date', '>=', $today)
            ->count();
        $offMarket = RoomOutOfOrder::where('type', OooType::OffMarket)
            ->where('from_date', '<=', $today)
            ->where('until_date', '>=', $today)
            ->count();
        $inactive = Room::where('is_active', false)->count();

        $estimatedOccupied = $occupied + \App\Models\Reservation::where('arrival_date', $today)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::Guaranteed])
            ->count();

        return [
            'occupied' => $occupied,
            'out_of_order' => $oooCount,
            'off_market' => $offMarket,
            'inactive' => $inactive,
            'estimated_occupied' => $estimatedOccupied,
            'total_rooms' => $totalRooms,
        ];
    }

    public function getHousekeepingActivity(): array
    {
        $vacantClean = Room::active()->where('status', RoomStatus::VacantClean)->count();
        $vacantCleanUnchecked = Room::active()->where('status', RoomStatus::VacantCleanUnchecked)->count();
        $occupiedClean = Room::active()->where('status', RoomStatus::OccupiedClean)->count();
        $occupiedDirty = Room::active()->where('status', RoomStatus::OccupiedDirty)->count();
        $vacantDirty = Room::active()->where('status', RoomStatus::VacantDirty)->count();
        $expectedDeparture = Room::active()->where('status', RoomStatus::ExpectedDeparture)->count();
        $dnd = Room::active()->where('status', RoomStatus::DoNotDisturb)->count();

        $totalCleaned = $vacantClean + $vacantCleanUnchecked + $occupiedClean;
        $totalUncleaned = $occupiedDirty + $vacantDirty;
        $availableToday = $vacantClean + $vacantCleanUnchecked + $vacantDirty;

        return [
            'vacant_clean' => $vacantClean,
            'vacant_clean_unchecked' => $vacantCleanUnchecked,
            'occupied_clean' => $occupiedClean,
            'total_cleaned' => $totalCleaned,
            'occupied_dirty' => $occupiedDirty,
            'vacant_dirty' => $vacantDirty,
            'expected_departure' => $expectedDeparture,
            'dnd' => $dnd,
            'available_today' => $availableToday,
            'total_uncleaned' => $totalUncleaned,
        ];
    }
}
