<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Traits\HasReportExport;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\SystemDate;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class NationalityStatistic extends Page implements HasTable
{
    use InteractsWithTable;
    use HasReportExport;

    protected string $view = 'filament.fo.pages.nationality-statistic';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::GlobeAlt;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Nationality Statistic';

    protected static ?string $title = 'Nationality Statistic';

    protected static ?string $slug = 'nationality-statistic';

    public ?string $reportMode = 'in_house';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $summaryData = [];

    public function mount(): void
    {
        $today = SystemDate::today();
        $this->startDate = $today;
        $this->endDate = Carbon::parse($today)->addDays(30)->toDateString();
        $this->loadSummary();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->schema([
                Select::make('reportMode')
                    ->label('Report Mode')
                    ->options([
                        'in_house' => 'In-House Guests',
                        'arrivals' => 'Arrivals Period',
                        'departures' => 'Departures Period',
                        'all_period' => 'All Reservations Period',
                    ])
                    ->default('in_house')
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadSummary()),
                DatePicker::make('startDate')
                    ->label('From')
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadSummary()),
                DatePicker::make('endDate')
                    ->label('To')
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadSummary()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->columns([
                TextColumn::make('nationality')
                    ->label('Nationality')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('guest_count')
                    ->label('Total Guests')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('percentage')
                    ->label('Percentage')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1) . '%'),
            ])
            ->defaultSort('guest_count', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $mode = $this->reportMode ?? 'in_house';

        $query = Guest::query()
            ->select('nationality', DB::raw('COUNT(*) as guest_count'), DB::raw('MIN(guests.id) as id'))
            ->selectRaw(
                'ROUND(COUNT(*) * 100.0 / NULLIF((SELECT COUNT(*) FROM guests g2 INNER JOIN reservations r2 ON g2.id = r2.guest_id WHERE '
                . $this->getSubQueryCondition()
                . '), 0), 1) as percentage'
            )
            ->join('reservations', 'guests.id', '=', 'reservations.guest_id');

        $this->applyModeFilter($query, $mode);

        return $query->groupBy('nationality');
    }

    protected function getSubQueryCondition(): string
    {
        $mode = $this->reportMode ?? 'in_house';

        if ($mode === 'in_house') {
            return "r2.status = 'checked_in'";
        }

        $start = $this->startDate ?? SystemDate::today();
        $end = $this->endDate ?? $start;

        return match ($mode) {
            'arrivals' => "r2.arrival_date BETWEEN '{$start}' AND '{$end}' AND r2.status NOT IN ('cancelled', 'no_show')",
            'departures' => "r2.departure_date BETWEEN '{$start}' AND '{$end}' AND r2.status NOT IN ('cancelled', 'no_show')",
            default => "r2.arrival_date <= '{$end}' AND r2.departure_date >= '{$start}' AND r2.status NOT IN ('cancelled', 'no_show')",
        };
    }

    protected function applyModeFilter(Builder $query, string $mode): void
    {
        if ($mode === 'in_house') {
            $query->where('reservations.status', ReservationStatus::CheckedIn);
            return;
        }

        $start = $this->startDate ?? SystemDate::today();
        $end = $this->endDate ?? $start;

        match ($mode) {
            'arrivals' => $query->whereBetween('reservations.arrival_date', [$start, $end]),
            'departures' => $query->whereBetween('reservations.departure_date', [$start, $end]),
            default => $query->where('reservations.arrival_date', '<=', $end)
                ->where('reservations.departure_date', '>=', $start),
        };

        $query->whereNotIn('reservations.status', [
            ReservationStatus::Cancelled,
            ReservationStatus::NoShow,
        ]);
    }

    public function loadSummary(): void
    {
        try {
            $mode = $this->reportMode ?? 'in_house';

        $query = Reservation::query()
            ->join('guests', 'reservations.guest_id', '=', 'guests.id');

        if ($mode === 'in_house') {
            $query->where('reservations.status', ReservationStatus::CheckedIn);
        } else {
            $start = $this->startDate ?? SystemDate::today();
            $end = $this->endDate ?? $start;

            match ($mode) {
                'arrivals' => $query->whereBetween('reservations.arrival_date', [$start, $end]),
                'departures' => $query->whereBetween('reservations.departure_date', [$start, $end]),
                default => $query->where('reservations.arrival_date', '<=', $end)
                    ->where('reservations.departure_date', '>=', $start),
            };

            $query->whereNotIn('reservations.status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
            ]);
        }

        $totalGuests = $query->count();
        $nationalities = (clone $query)->distinct('guests.nationality')->count('guests.nationality');

        $topNationality = (clone $query)
            ->select('guests.nationality', DB::raw('COUNT(*) as cnt'))
            ->groupBy('guests.nationality')
            ->orderByDesc('cnt')
            ->first();

        $this->summaryData = [
            'total_guests' => $totalGuests,
            'total_nationalities' => $nationalities,
            'top_nationality' => $topNationality?->nationality ?? '-',
            'top_count' => $topNationality?->cnt ?? 0,
        ];
        } catch (\Throwable $e) {
            report($e);
            $this->summaryData = ['total_guests' => 0, 'total_nationalities' => 0, 'top_nationality' => '-', 'top_count' => 0];
        }
    }

    protected function getReportTitle(): string
    {
        return 'Nationality Statistic';
    }

    protected function getExportData(): array
    {
        $rows = $this->getTableQuery()->get()->map(fn ($item) => [
            $item->nationality ?? '-',
            (string) $item->guest_count,
            number_format((float) $item->percentage, 1) . '%',
        ])->toArray();

        return [
            'headers' => ['Nationality', 'Total Guests', 'Percentage'],
            'rows' => $rows,
            'subtitle' => 'Mode: ' . ucfirst(str_replace('_', ' ', $this->reportMode ?? 'in_house')),
            'summary' => [
                ['label' => 'Total Guests', 'value' => (string) ($this->summaryData['total_guests'] ?? 0)],
                ['label' => 'Nationalities', 'value' => (string) ($this->summaryData['total_nationalities'] ?? 0)],
            ],
        ];
    }
}
