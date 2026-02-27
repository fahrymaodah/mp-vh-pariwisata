<?php

declare(strict_types=1);

namespace App\Filament\Hk\Widgets;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomOutOfOrder;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HkStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $vacantClean = Room::active()->where('status', RoomStatus::VacantClean)->count();
        $vacantDirty = Room::active()->where('status', RoomStatus::VacantDirty)->count();
        $vacantUnchecked = Room::active()->where('status', RoomStatus::VacantCleanUnchecked)->count();
        $occupiedDirty = Room::active()->where('status', RoomStatus::OccupiedDirty)->count();
        $occupiedClean = Room::active()->where('status', RoomStatus::OccupiedClean)->count();
        $oooRooms = Room::active()->where('status', RoomStatus::OutOfOrder)->count();

        $totalActive = Room::active()->count();

        return [
            Stat::make('Vacant Clean', (string) $vacantClean)
                ->description('Ready for check-in')
                ->icon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make('Vacant Dirty', (string) $vacantDirty)
                ->description('Needs cleaning')
                ->icon(Heroicon::ExclamationTriangle)
                ->color('danger'),

            Stat::make('Vacant Unchecked', (string) $vacantUnchecked)
                ->description('Needs inspection')
                ->icon(Heroicon::MagnifyingGlass)
                ->color('warning'),

            Stat::make('Occupied Dirty', (string) $occupiedDirty)
                ->description('In-house, needs service')
                ->icon(Heroicon::Home)
                ->color('warning'),

            Stat::make('Occupied Clean', (string) $occupiedClean)
                ->description('In-house, serviced')
                ->icon(Heroicon::HomeModern)
                ->color('info'),

            Stat::make('Out of Order', (string) $oooRooms)
                ->description('Unavailable rooms')
                ->icon(Heroicon::Wrench)
                ->color('gray'),
        ];
    }
}
