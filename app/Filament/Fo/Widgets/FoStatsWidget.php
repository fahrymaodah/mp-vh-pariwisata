<?php

declare(strict_types=1);

namespace App\Filament\Fo\Widgets;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FoStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $today = SystemDate::today();
        $totalRooms = Room::active()->count();

        $occupiedRooms = Room::whereIn('status', [
            RoomStatus::OccupiedClean,
            RoomStatus::OccupiedDirty,
            RoomStatus::DoNotDisturb,
            RoomStatus::ExpectedDeparture,
        ])->count();

        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100, 1)
            : 0;

        $availableRooms = Room::active()
            ->where('status', RoomStatus::VacantClean)
            ->count();

        $todayArrivals = Reservation::whereDate('arrival_date', $today)
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::Confirmed,
                ReservationStatus::SixPm,
                ReservationStatus::OralConfirmed,
            ])
            ->count();

        $todayDepartures = Reservation::where('status', ReservationStatus::CheckedIn)
            ->whereDate('departure_date', $today)
            ->count();

        $inHouseCount = Reservation::where('status', ReservationStatus::CheckedIn)->count();

        return [
            Stat::make('Occupancy', $occupancyRate . '%')
                ->description($occupiedRooms . '/' . $totalRooms . ' rooms')
                ->icon(Heroicon::ChartBar)
                ->color($occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Available Rooms', (string) $availableRooms)
                ->description('Vacant Clean')
                ->icon(Heroicon::Key)
                ->color('success'),

            Stat::make('In-House', (string) $inHouseCount)
                ->description('Checked-in guests')
                ->icon(Heroicon::Users)
                ->color('info'),

            Stat::make("Today's Arrivals", (string) $todayArrivals)
                ->description('Expected check-ins')
                ->icon(Heroicon::ArrowDownOnSquare)
                ->color('primary'),

            Stat::make("Today's Departures", (string) $todayDepartures)
                ->description('Expected check-outs')
                ->icon(Heroicon::ArrowUpOnSquare)
                ->color('warning'),
        ];
    }
}
