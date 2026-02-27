<?php

declare(strict_types=1);

namespace App\Filament\Sales\Pages;

use App\Enums\GuestType;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\SalesActivity;
use App\Models\SalesBudget;
use App\Models\SalesOpportunity;
use App\Models\SalesTask;
use App\Models\SegmentBudget;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class SalesReports extends Page
{
    protected string $view = 'filament.sales.pages.sales-reports';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBar;
    protected static string | UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Sales Reports';
    protected static ?string $title = 'Sales Reports';

    public string $activeReport = 'sales-performance';
    public string $periodFrom = '';
    public string $periodTo = '';

    public function mount(): void
    {
        $this->periodFrom = now()->startOfMonth()->format('Y-m-d');
        $this->periodTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function setReport(string $report): void
    {
        $this->activeReport = $report;
    }

    public function getReportDataProperty(): array
    {
        return match ($this->activeReport) {
            'sales-performance' => $this->getSalesPerformance(),
            'opportunity-pipeline' => $this->getOpportunityPipeline(),
            'activity-summary' => $this->getActivitySummary(),
            'task-completion' => $this->getTaskCompletion(),
            'budget-vs-actual' => $this->getBudgetVsActual(),
            'segment-statistics' => $this->getSegmentStatistics(),
            'guest-production' => $this->getGuestProduction(),
            'repeater-guest' => $this->getRepeaterGuestList(),
            'guest-birthday' => $this->getGuestBirthdayList(),
            'nationality-stats' => $this->getNationalityStatistics(),
            'reservation-by-sales' => $this->getReservationBySales(),
            'company-production' => $this->getCompanyProduction(),
            'source-statistics' => $this->getSourceStatistics(),
            'competitor-analysis' => $this->getCompetitorAnalysis(),
            default => [],
        };
    }

    protected function getSalesPerformance(): array
    {
        return SalesActivity::query()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_activities'),
                DB::raw('SUM(target_amount) as total_target'),
                DB::raw('SUM(CASE WHEN is_finished = 1 THEN 1 ELSE 0 END) as finished'),
                DB::raw('SUM(CASE WHEN is_finished = 0 THEN 1 ELSE 0 END) as pending'),
            )
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(fn ($row) => [
                'sales_person' => $row->user?->name ?? '-',
                'total_activities' => $row->total_activities,
                'total_target' => number_format((float) $row->total_target, 0, ',', '.'),
                'finished' => $row->finished,
                'pending' => $row->pending,
                'completion_rate' => $row->total_activities > 0
                    ? round(($row->finished / $row->total_activities) * 100, 1) . '%'
                    : '0%',
            ])
            ->toArray();
    }

    protected function getOpportunityPipeline(): array
    {
        return SalesOpportunity::query()
            ->select(
                'stage_id',
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(target_amount) as total_amount'),
                DB::raw('AVG(probability) as avg_probability'),
            )
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->groupBy('stage_id', 'status')
            ->with('stage:id,name')
            ->get()
            ->map(fn ($row) => [
                'stage' => $row->stage?->name ?? '-',
                'status' => ucfirst($row->status),
                'count' => $row->count,
                'total_amount' => number_format((float) $row->total_amount, 0, ',', '.'),
                'avg_probability' => round((float) $row->avg_probability, 1) . '%',
            ])
            ->toArray();
    }

    protected function getActivitySummary(): array
    {
        return SalesActivity::query()
            ->select(
                'user_id',
                'priority',
                DB::raw('COUNT(*) as count'),
            )
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->groupBy('user_id', 'priority')
            ->with('user:id,name')
            ->orderBy('user_id')
            ->get()
            ->map(fn ($row) => [
                'sales_person' => $row->user?->name ?? '-',
                'priority' => $row->priority,
                'count' => $row->count,
            ])
            ->toArray();
    }

    protected function getTaskCompletion(): array
    {
        return SalesTask::query()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN is_completed = 0 AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue'),
            )
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(fn ($row) => [
                'sales_person' => $row->user?->name ?? '-',
                'total' => $row->total,
                'completed' => $row->completed,
                'overdue' => $row->overdue,
                'completion_rate' => $row->total > 0
                    ? round(($row->completed / $row->total) * 100, 1) . '%'
                    : '0%',
            ])
            ->toArray();
    }

    protected function getBudgetVsActual(): array
    {
        return SalesBudget::query()
            ->whereBetween('month', [$this->periodFrom, $this->periodTo])
            ->with('user:id,name')
            ->get()
            ->map(fn ($row) => [
                'sales_person' => $row->user?->name ?? '-',
                'month' => Carbon::parse($row->month)->format('F Y'),
                'lodging_budget' => number_format((float) $row->lodging, 0, ',', '.'),
                'fb_budget' => number_format((float) $row->fb, 0, ',', '.'),
                'others_budget' => number_format((float) $row->others, 0, ',', '.'),
                'room_nights' => $row->room_nights,
            ])
            ->toArray();
    }

    protected function getSegmentStatistics(): array
    {
        return SegmentBudget::query()
            ->whereBetween('date', [$this->periodFrom, $this->periodTo])
            ->with('segment:id,code,description')
            ->get()
            ->map(fn ($row) => [
                'segment' => $row->segment?->description ?? '-',
                'month' => Carbon::parse($row->date)->format('F Y'),
                'budget_rooms' => $row->budget_rooms,
                'budget_persons' => $row->budget_persons,
                'budget_lodging' => number_format((float) $row->budget_lodging, 0, ',', '.'),
            ])
            ->toArray();
    }

    protected function getGuestProduction(): array
    {
        return Guest::query()
            ->select(
                'guests.id',
                'guests.guest_no',
                'guests.name',
                'guests.first_name',
                'guests.type',
                'guests.city',
            )
            ->withCount(['reservations' => function ($q) {
                $q->whereBetween('arrival_date', [$this->periodFrom, $this->periodTo]);
            }])
            ->having('reservations_count', '>', 0)
            ->orderByDesc('reservations_count')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'guest_no' => $row->guest_no,
                'name' => trim(($row->name ?? '') . ' ' . ($row->first_name ?? '')),
                'type' => $row->type?->value ?? '-',
                'city' => $row->city ?? '-',
                'total_reservations' => $row->reservations_count,
            ])
            ->toArray();
    }

    protected function getRepeaterGuestList(): array
    {
        return Guest::query()
            ->select('guests.id', 'guests.guest_no', 'guests.name', 'guests.first_name', 'guests.city', 'guests.phone', 'guests.email')
            ->withCount('reservations')
            ->having('reservations_count', '>=', 2)
            ->orderByDesc('reservations_count')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'guest_no' => $row->guest_no,
                'name' => trim(($row->name ?? '') . ' ' . ($row->first_name ?? '')),
                'city' => $row->city ?? '-',
                'phone' => $row->phone ?? '-',
                'email' => $row->email ?? '-',
                'total_stays' => $row->reservations_count,
            ])
            ->toArray();
    }

    protected function getGuestBirthdayList(): array
    {
        $month = Carbon::parse($this->periodFrom)->month;

        return Guest::query()
            ->whereMonth('birth_date', $month)
            ->orderByRaw('DAY(birth_date) ASC')
            ->limit(100)
            ->get()
            ->map(fn ($row) => [
                'guest_no' => $row->guest_no,
                'name' => trim(($row->name ?? '') . ' ' . ($row->first_name ?? '')),
                'birthday' => $row->birth_date ? Carbon::parse($row->birth_date)->format('d F') : '-',
                'phone' => $row->phone ?? '-',
                'email' => $row->email ?? '-',
            ])
            ->toArray();
    }

    protected function getNationalityStatistics(): array
    {
        return Guest::query()
            ->select('nationality', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nationality')
            ->where('nationality', '!=', '')
            ->groupBy('nationality')
            ->orderByDesc('total')
            ->limit(30)
            ->get()
            ->map(fn ($row) => [
                'nationality' => $row->nationality,
                'total_guests' => $row->total,
            ])
            ->toArray();
    }

    protected function getReservationBySales(): array
    {
        return Reservation::query()
            ->select(
                'created_by',
                DB::raw('COUNT(*) as total_reservations'),
                DB::raw('SUM(room_rate * nights) as total_revenue'),
            )
            ->whereBetween('arrival_date', [$this->periodFrom, $this->periodTo])
            ->whereNotNull('created_by')
            ->groupBy('created_by')
            ->with('createdBy:id,name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn ($row) => [
                'sales_person' => $row->createdBy?->name ?? '-',
                'total_reservations' => $row->total_reservations,
                'total_revenue' => number_format((float) $row->total_revenue, 0, ',', '.'),
            ])
            ->toArray();
    }

    protected function getCompanyProduction(): array
    {
        return Guest::query()
            ->where('type', GuestType::Company)
            ->select('guests.id', 'guests.guest_no', 'guests.name', 'guests.company_title', 'guests.city')
            ->withCount(['reservations' => function ($q) {
                $q->whereBetween('arrival_date', [$this->periodFrom, $this->periodTo]);
            }])
            ->having('reservations_count', '>', 0)
            ->orderByDesc('reservations_count')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'guest_no' => $row->guest_no,
                'company' => $row->company_title ?? $row->name,
                'city' => $row->city ?? '-',
                'total_reservations' => $row->reservations_count,
            ])
            ->toArray();
    }

    protected function getSourceStatistics(): array
    {
        return SalesOpportunity::query()
            ->select('source_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(target_amount) as total_amount'))
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->whereNotNull('source_id')
            ->groupBy('source_id')
            ->with('source:id,name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'source' => $row->source?->name ?? '-',
                'total_opportunities' => $row->total,
                'total_amount' => number_format((float) $row->total_amount, 0, ',', '.'),
            ])
            ->toArray();
    }

    protected function getCompetitorAnalysis(): array
    {
        return SalesActivity::query()
            ->select('competitor', DB::raw('COUNT(*) as mentions'))
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->whereNotNull('competitor')
            ->where('competitor', '!=', '')
            ->groupBy('competitor')
            ->orderByDesc('mentions')
            ->get()
            ->map(fn ($row) => [
                'competitor' => $row->competitor,
                'mentions' => $row->mentions,
            ])
            ->toArray();
    }
}
