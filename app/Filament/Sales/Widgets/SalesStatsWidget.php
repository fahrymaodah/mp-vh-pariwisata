<?php

declare(strict_types=1);

namespace App\Filament\Sales\Widgets;

use App\Models\SalesActivity;
use App\Models\SalesOpportunity;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeActivities = SalesActivity::where('is_finished', false)->count();
        $finishedActivities = SalesActivity::where('is_finished', true)->count();

        $openOpportunities = SalesOpportunity::whereNull('finish_date')
            ->orWhere('finish_date', '>=', now())
            ->count();

        $pipelineValue = SalesOpportunity::whereNull('finish_date')
            ->sum('target_amount');

        $avgProbability = SalesOpportunity::whereNull('finish_date')
            ->avg('probability');

        $upcomingActions = SalesActivity::where('is_finished', false)
            ->where('next_action_date', '<=', now()->addDays(7))
            ->count();

        return [
            Stat::make('Active Activities', (string) $activeActivities)
                ->description($finishedActivities . ' completed')
                ->icon(Heroicon::Briefcase)
                ->color('primary'),

            Stat::make('Open Opportunities', (string) $openOpportunities)
                ->description('In pipeline')
                ->icon(Heroicon::RocketLaunch)
                ->color('info'),

            Stat::make('Pipeline Value', 'Rp ' . number_format((float) $pipelineValue, 0, ',', '.'))
                ->description('Total target amount')
                ->icon(Heroicon::CurrencyDollar)
                ->color('success'),

            Stat::make('Avg Probability', round((float) $avgProbability, 1) . '%')
                ->description('Win probability')
                ->icon(Heroicon::ChartBar)
                ->color('warning'),

            Stat::make('Upcoming Actions', (string) $upcomingActions)
                ->description('Within 7 days')
                ->icon(Heroicon::CalendarDays)
                ->color($upcomingActions > 0 ? 'danger' : 'gray'),
        ];
    }
}
