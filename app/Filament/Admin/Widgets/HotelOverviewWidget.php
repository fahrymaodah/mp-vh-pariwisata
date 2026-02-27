<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HotelOverviewWidget extends BaseWidget
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

        $totalGuests = Guest::count();

        $inHouseCount = Reservation::where('status', ReservationStatus::CheckedIn)->count();

        $todayRevenue = Invoice::whereDate('created_at', $today)
            ->sum('total_sales');

        $monthRevenue = Invoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_sales');

        return [
            Stat::make('Total Rooms', (string) $totalRooms)
                ->description('Active rooms')
                ->icon(Heroicon::BuildingOffice)
                ->color('primary'),

            Stat::make('Occupancy Rate', $occupancyRate . '%')
                ->description($occupiedRooms . ' of ' . $totalRooms . ' rooms')
                ->icon(Heroicon::ChartBar)
                ->color($occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger')),

            Stat::make('In-House Guests', (string) $inHouseCount)
                ->description('Currently checked in')
                ->icon(Heroicon::Users)
                ->color('info'),

            Stat::make('Total Guests', (string) $totalGuests)
                ->description('All registered guests')
                ->icon(Heroicon::UserGroup)
                ->color('gray'),

            Stat::make('Today Revenue', 'Rp ' . number_format((float) $todayRevenue, 0, ',', '.'))
                ->description('Revenue today')
                ->icon(Heroicon::Banknotes)
                ->color('success'),

            Stat::make('Month Revenue', 'Rp ' . number_format((float) $monthRevenue, 0, ',', '.'))
                ->description(now()->format('F Y'))
                ->icon(Heroicon::CurrencyDollar)
                ->color('success'),
        ];
    }
}
