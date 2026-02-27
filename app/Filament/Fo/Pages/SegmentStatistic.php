<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Traits\HasReportExport;
use App\Models\Reservation;
use App\Models\Segment;
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

class SegmentStatistic extends Page implements HasTable
{
    use InteractsWithTable;
    use HasReportExport;

    protected string $view = 'filament.fo.pages.segment-statistic';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartPie;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 21;

    protected static ?string $navigationLabel = 'Segment Statistic';

    protected static ?string $title = 'Segment Statistic';

    protected static ?string $slug = 'segment-statistic';

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
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('description')
                    ->label('Segment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reservation_count')
                    ->label('Reservations')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('room_nights')
                    ->label('Room Nights')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_revenue')
                    ->label('Est. Revenue')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('avg_rate')
                    ->label('Avg. Rate')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('percentage')
                    ->label('%')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1) . '%'),
            ])
            ->defaultSort('reservation_count', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $mode = $this->reportMode ?? 'in_house';

        $query = Segment::query()
            ->select(
                'segments.id',
                'segments.code',
                'segments.description',
                DB::raw('COUNT(reservations.id) as reservation_count'),
                DB::raw('COALESCE(SUM(reservations.nights), 0) as room_nights'),
                DB::raw('COALESCE(SUM(reservations.room_rate * reservations.nights), 0) as total_revenue'),
                DB::raw('COALESCE(AVG(reservations.room_rate), 0) as avg_rate'),
            )
            ->selectRaw(
                'ROUND(COUNT(reservations.id) * 100.0 / NULLIF((SELECT COUNT(*) FROM reservations r2 WHERE '
                . $this->getSubQueryCondition()
                . '), 0), 1) as percentage'
            )
            ->leftJoin('reservations', function ($join) use ($mode) {
                $join->on('segments.id', '=', 'reservations.segment_id');
                $this->applyJoinConditions($join, $mode);
            })
            ->where('segments.is_active', true)
            ->groupBy('segments.id', 'segments.code', 'segments.description');

        return $query;
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
            default => "r2.arrival_date <= '{$end}' AND r2.departure_date >= '{$start}' AND r2.status NOT IN ('cancelled', 'no_show')",
        };
    }

    protected function applyJoinConditions($join, string $mode): void
    {
        if ($mode === 'in_house') {
            $join->where('reservations.status', '=', ReservationStatus::CheckedIn->value);
            return;
        }

        $start = $this->startDate ?? SystemDate::today();
        $end = $this->endDate ?? $start;

        match ($mode) {
            'arrivals' => $join->whereBetween('reservations.arrival_date', [$start, $end]),
            default => $join->where('reservations.arrival_date', '<=', $end)
                ->where('reservations.departure_date', '>=', $start),
        };

        $join->whereNotIn('reservations.status', [
            ReservationStatus::Cancelled->value,
            ReservationStatus::NoShow->value,
        ]);
    }

    public function loadSummary(): void
    {
        try {
            $mode = $this->reportMode ?? 'in_house';

        $query = Reservation::query()->whereNotNull('segment_id');

        if ($mode === 'in_house') {
            $query->where('status', ReservationStatus::CheckedIn);
        } else {
            $start = $this->startDate ?? SystemDate::today();
            $end = $this->endDate ?? $start;

            match ($mode) {
                'arrivals' => $query->whereBetween('arrival_date', [$start, $end]),
                default => $query->where('arrival_date', '<=', $end)
                    ->where('departure_date', '>=', $start),
            };

            $query->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
            ]);
        }

        $totalReservations = $query->count();
        $totalRevenue = (clone $query)->sum(DB::raw('room_rate * nights'));
        $avgRate = (clone $query)->avg('room_rate');

        $topSegment = (clone $query)
            ->select('segment_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('segment_id')
            ->orderByDesc('cnt')
            ->with('segment')
            ->first();

        $this->summaryData = [
            'total_reservations' => $totalReservations,
            'total_revenue' => (float) $totalRevenue,
            'avg_rate' => (float) ($avgRate ?? 0),
            'top_segment' => $topSegment?->segment?->description ?? '-',
            'top_count' => $topSegment?->cnt ?? 0,
        ];
        } catch (\Throwable $e) {
            report($e);
            $this->summaryData = ['total_reservations' => 0, 'total_revenue' => 0, 'avg_rate' => 0, 'top_segment' => '-', 'top_count' => 0];
        }
    }

    protected function getReportTitle(): string
    {
        return 'Segment Statistic';
    }

    protected function getExportData(): array
    {
        $rows = $this->getTableQuery()->get()->map(fn ($item) => [
            $item->code,
            $item->description,
            (string) $item->reservation_count,
            (string) $item->room_nights,
            'Rp ' . number_format((float) $item->total_revenue, 0, ',', '.'),
            'Rp ' . number_format((float) $item->avg_rate, 0, ',', '.'),
            number_format((float) $item->percentage, 1) . '%',
        ])->toArray();

        return [
            'headers' => ['Code', 'Segment', 'Reservations', 'Room Nights', 'Est. Revenue', 'Avg. Rate', '%'],
            'rows' => $rows,
            'subtitle' => 'Mode: ' . ucfirst(str_replace('_', ' ', $this->reportMode ?? 'in_house')),
            'orientation' => 'landscape',
        ];
    }
}
