<?php

declare(strict_types=1);

namespace App\Filament\Telop\Widgets;

use App\Enums\ReservationStatus;
use App\Models\CallLog;
use App\Models\GuestLocator;
use App\Models\GuestMessage;
use App\Models\Reservation;
use App\Models\SystemDate;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TelopStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $today = SystemDate::today();

        $inHouseCount = Reservation::where('status', ReservationStatus::CheckedIn)->count();

        $unreadMessages = GuestMessage::where('is_read', false)->count();

        $activeLocators = GuestLocator::where('is_active', true)->count();

        $todayCalls = CallLog::whereDate('call_date', $today)->count();

        $unpostedCalls = CallLog::where('is_posted', false)->count();

        return [
            Stat::make('In-House Guests', (string) $inHouseCount)
                ->description('Currently checked in')
                ->icon(Heroicon::Users)
                ->color('primary'),

            Stat::make('Unread Messages', (string) $unreadMessages)
                ->description('Pending delivery')
                ->icon(Heroicon::Envelope)
                ->color($unreadMessages > 0 ? 'danger' : 'success'),

            Stat::make('Active Locators', (string) $activeLocators)
                ->description('Guest locations')
                ->icon(Heroicon::MapPin)
                ->color($activeLocators > 0 ? 'warning' : 'gray'),

            Stat::make("Today's Calls", (string) $todayCalls)
                ->description('Call logs today')
                ->icon(Heroicon::Phone)
                ->color('info'),

            Stat::make('Unposted Calls', (string) $unpostedCalls)
                ->description('Not yet billed')
                ->icon(Heroicon::ExclamationCircle)
                ->color($unpostedCalls > 0 ? 'warning' : 'success'),
        ];
    }
}
