<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\RoomCategory;
use App\Models\Segment;
use App\Models\SystemDate;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReservationReports extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::receptionRoles()) ?? false;
    }

    protected static ?string $title = 'Reservation Reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBar;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.fo.pages.reservation-reports';

    public ?string $reportType = 'summary';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $reportData = [];

    public function mount(): void
    {
        $today = SystemDate::today();
        $this->startDate = $today;
        $this->endDate = Carbon::parse($today)->addDays(6)->toDateString();
        $this->loadReport();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('reportType')
                ->label('Report Type')
                ->options([
                    'summary' => 'Reservation Summary',
                    'by_category' => 'By Room Category',
                    'by_segment' => 'By Segment',
                    'by_status' => 'By Status',
                    'cancellation' => 'Cancellation Report',
                    'no_show' => 'No-Show Report',
                    'forecast' => 'Occupancy Forecast',
                ])
                ->default('summary')
                ->live()
                ->afterStateUpdated(fn () => $this->loadReport()),
            DatePicker::make('startDate')
                ->label('From')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->loadReport()),
            DatePicker::make('endDate')
                ->label('To')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->loadReport()),
        ]);
    }

    public function loadReport(): void
    {
        if (! $this->startDate || ! $this->endDate) {
            return;
        }

        $this->reportData = match ($this->reportType) {
            'summary' => $this->buildSummaryReport(),
            'by_category' => $this->buildCategoryReport(),
            'by_segment' => $this->buildSegmentReport(),
            'by_status' => $this->buildStatusReport(),
            'cancellation' => $this->buildCancellationReport(),
            'no_show' => $this->buildNoShowReport(),
            'forecast' => $this->buildForecastReport(),
            default => [],
        };
    }

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Reservation::query()
            ->where('arrival_date', '<=', $this->endDate)
            ->where('departure_date', '>=', $this->startDate);
    }

    private function buildSummaryReport(): array
    {
        $base = $this->baseQuery();

        $total = (clone $base)->count();
        $active = (clone $base)->active()->count();
        $cancelled = (clone $base)->where('status', ReservationStatus::Cancelled)->count();
        $noShow = (clone $base)->where('status', ReservationStatus::NoShow)->count();
        $checkedIn = (clone $base)->where('status', ReservationStatus::CheckedIn)->count();
        $checkedOut = (clone $base)->where('status', ReservationStatus::CheckedOut)->count();

        $totalRevenue = (clone $base)->active()->sum(\DB::raw('room_rate * nights'));
        $avgRate = (clone $base)->active()->avg('room_rate');
        $avgNights = (clone $base)->active()->avg('nights');
        $totalRoomNights = (clone $base)->active()->sum('nights');

        return [
            'type' => 'summary',
            'metrics' => [
                ['label' => 'Total Reservations', 'value' => $total],
                ['label' => 'Active Reservations', 'value' => $active],
                ['label' => 'Checked In', 'value' => $checkedIn],
                ['label' => 'Checked Out', 'value' => $checkedOut],
                ['label' => 'Cancelled', 'value' => $cancelled],
                ['label' => 'No Shows', 'value' => $noShow],
                ['label' => 'Total Room Nights', 'value' => $totalRoomNights],
                ['label' => 'Avg. Stay (nights)', 'value' => round((float) $avgNights, 1)],
                ['label' => 'Avg. Room Rate', 'value' => 'IDR ' . number_format((float) $avgRate, 0, ',', '.')],
                ['label' => 'Est. Total Revenue', 'value' => 'IDR ' . number_format((float) $totalRevenue, 0, ',', '.')],
            ],
        ];
    }

    private function buildCategoryReport(): array
    {
        $categories = RoomCategory::all();
        $rows = [];

        foreach ($categories as $cat) {
            $q = (clone $this->baseQuery())->where('room_category_id', $cat->id);

            $rows[] = [
                'category' => $cat->code . ' — ' . $cat->name,
                'total' => (clone $q)->count(),
                'active' => (clone $q)->active()->count(),
                'room_nights' => (clone $q)->active()->sum('nights'),
                'avg_rate' => round((float) (clone $q)->active()->avg('room_rate'), 0),
                'revenue' => round((float) (clone $q)->active()->sum(\DB::raw('room_rate * nights')), 0),
            ];
        }

        return ['type' => 'category', 'rows' => $rows];
    }

    private function buildSegmentReport(): array
    {
        $segments = Segment::all();
        $rows = [];

        foreach ($segments as $seg) {
            $q = (clone $this->baseQuery())->where('segment_id', $seg->id);
            $count = (clone $q)->count();
            if ($count === 0) {
                continue;
            }

            $rows[] = [
                'segment' => $seg->code . ' — ' . $seg->description,
                'total' => $count,
                'active' => (clone $q)->active()->count(),
                'room_nights' => (clone $q)->active()->sum('nights'),
                'revenue' => round((float) (clone $q)->active()->sum(\DB::raw('room_rate * nights')), 0),
            ];
        }

        return ['type' => 'segment', 'rows' => $rows];
    }

    private function buildStatusReport(): array
    {
        $rows = [];

        foreach (ReservationStatus::cases() as $status) {
            $count = (clone $this->baseQuery())->where('status', $status)->count();
            if ($count === 0) {
                continue;
            }

            $rows[] = [
                'status' => $status->label(),
                'color' => $status->color(),
                'count' => $count,
                'room_nights' => (clone $this->baseQuery())->where('status', $status)->sum('nights'),
            ];
        }

        return ['type' => 'status', 'rows' => $rows];
    }

    private function buildCancellationReport(): array
    {
        $cancellations = Reservation::with(['guest', 'roomCategory', 'cancelledByUser'])
            ->where('status', ReservationStatus::Cancelled)
            ->where('cancelled_at', '>=', $this->startDate)
            ->where('cancelled_at', '<=', Carbon::parse($this->endDate)->endOfDay())
            ->orderByDesc('cancelled_at')
            ->get();

        return [
            'type' => 'cancellation',
            'rows' => $cancellations->map(fn ($r) => [
                'reservation_no' => $r->reservation_no,
                'guest' => $r->guest?->full_name ?? '-',
                'category' => $r->roomCategory?->code ?? '-',
                'arrival' => $r->arrival_date?->format('d M Y'),
                'departure' => $r->departure_date?->format('d M Y'),
                'cancelled_at' => $r->cancelled_at?->format('d M Y H:i'),
                'cancelled_by' => $r->cancelledByUser?->name ?? '-',
                'reason' => $r->cancel_reason ?? '-',
            ])->toArray(),
        ];
    }

    private function buildNoShowReport(): array
    {
        $noShows = Reservation::with(['guest', 'roomCategory'])
            ->where('status', ReservationStatus::NoShow)
            ->where('arrival_date', '>=', $this->startDate)
            ->where('arrival_date', '<=', $this->endDate)
            ->orderBy('arrival_date')
            ->get();

        return [
            'type' => 'no_show',
            'rows' => $noShows->map(fn ($r) => [
                'reservation_no' => $r->reservation_no,
                'guest' => $r->guest?->full_name ?? '-',
                'category' => $r->roomCategory?->code ?? '-',
                'arrival' => $r->arrival_date?->format('d M Y'),
                'nights' => $r->nights,
                'rate' => $r->room_rate,
            ])->toArray(),
        ];
    }

    private function buildForecastReport(): array
    {
        $categories = RoomCategory::withCount(['rooms' => fn ($q) => $q->where('is_active', true)])->get();

        $dates = [];
        $current = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $dayData = ['date' => $current->format('D d M'), 'categories' => []];

            $totalRooms = 0;
            $totalOccupied = 0;

            foreach ($categories as $cat) {
                $occupied = Reservation::where('room_category_id', $cat->id)
                    ->where('arrival_date', '<=', $dateStr)
                    ->where('departure_date', '>', $dateStr)
                    ->active()
                    ->where('is_room_sharer', false)
                    ->count();

                $dayData['categories'][$cat->code] = [
                    'total' => $cat->rooms_count,
                    'occupied' => $occupied,
                    'available' => max(0, $cat->rooms_count - $occupied),
                ];

                $totalRooms += $cat->rooms_count;
                $totalOccupied += $occupied;
            }

            $dayData['total_rooms'] = $totalRooms;
            $dayData['total_occupied'] = $totalOccupied;
            $dayData['occupancy_pct'] = $totalRooms > 0 ? round(($totalOccupied / $totalRooms) * 100, 1) : 0;

            $dates[] = $dayData;
            $current->addDay();
        }

        return [
            'type' => 'forecast',
            'categories' => $categories->pluck('code')->toArray(),
            'dates' => $dates,
        ];
    }
}
