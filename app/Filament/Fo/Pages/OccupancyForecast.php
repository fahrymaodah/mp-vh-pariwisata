<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Traits\HasReportExport;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\SystemDate;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class OccupancyForecast extends Page
{
    use HasReportExport;

    protected string $view = 'filament.fo.pages.occupancy-forecast';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::PresentationChartLine;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Occupancy Forecast';

    protected static ?string $title = 'Occupancy Forecast';

    protected static ?string $slug = 'occupancy-forecast';

    public string $forecastMode = 'daily';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $forecastData = [];

    public array $summaryData = [];

    public function mount(): void
    {
        $today = SystemDate::today();
        $this->startDate = $today;
        $this->endDate = Carbon::parse($today)->addDays(30)->toDateString();
        $this->loadForecast();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->schema([
                Select::make('forecastMode')
                    ->label('Display')
                    ->options([
                        'daily' => 'Daily',
                        'monthly' => 'Monthly',
                        'annual' => 'Annual',
                    ])
                    ->default('daily')
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->adjustDates();
                        $this->loadForecast();
                    }),
                DatePicker::make('startDate')
                    ->label('From')
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadForecast()),
                DatePicker::make('endDate')
                    ->label('To')
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadForecast()),
            ]),
        ]);
    }

    public function adjustDates(): void
    {
        $today = Carbon::parse(SystemDate::today());

        $this->startDate = match ($this->forecastMode) {
            'monthly' => $today->copy()->startOfMonth()->toDateString(),
            'annual' => $today->copy()->startOfYear()->toDateString(),
            default => $today->toDateString(),
        };

        $this->endDate = match ($this->forecastMode) {
            'monthly' => $today->copy()->addMonths(11)->endOfMonth()->toDateString(),
            'annual' => $today->copy()->addYears(2)->endOfYear()->toDateString(),
            default => $today->copy()->addDays(30)->toDateString(),
        };
    }

    public function loadForecast(): void
    {
        try {
            $totalRooms = Room::active()->count();

            if ($totalRooms === 0) {
                $this->forecastData = [];
                $this->summaryData = ['total_rooms' => 0, 'avg_occupancy' => 0, 'max_occupancy' => 0, 'min_occupancy' => 0];
                return;
            }

            $start = Carbon::parse($this->startDate ?? SystemDate::today());
            $end = Carbon::parse($this->endDate ?? $start->copy()->addDays(30));

            $activeStatuses = [
                ReservationStatus::Guaranteed->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::SixPm->value,
                ReservationStatus::OralConfirmed->value,
                ReservationStatus::Tentative->value,
                ReservationStatus::CheckedIn->value,
            ];

            $this->forecastData = match ($this->forecastMode) {
                'monthly' => $this->buildMonthlyForecast($start, $end, $totalRooms, $activeStatuses),
                'annual' => $this->buildAnnualForecast($start, $end, $totalRooms, $activeStatuses),
                default => $this->buildDailyForecast($start, $end, $totalRooms, $activeStatuses),
            };

            // Calculate summary
            $occupancies = array_column($this->forecastData, 'occupancy');
            $this->summaryData = [
                'total_rooms' => $totalRooms,
                'avg_occupancy' => count($occupancies) > 0 ? round(array_sum($occupancies) / count($occupancies), 1) : 0,
                'max_occupancy' => count($occupancies) > 0 ? max($occupancies) : 0,
                'min_occupancy' => count($occupancies) > 0 ? min($occupancies) : 0,
            ];
        } catch (\Throwable $e) {
            report($e);
            $this->forecastData = [];
            $this->summaryData = ['total_rooms' => 0, 'avg_occupancy' => 0, 'max_occupancy' => 0, 'min_occupancy' => 0];
        }
    }

    protected function buildDailyForecast(Carbon $start, Carbon $end, int $totalRooms, array $activeStatuses): array
    {
        $data = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

            $reservedRooms = Reservation::where('arrival_date', '<=', $dateStr)
                ->where('departure_date', '>', $dateStr)
                ->whereIn('status', $activeStatuses)
                ->count();

            $occupancy = round(($reservedRooms / $totalRooms) * 100, 1);

            $revenue = Reservation::where('arrival_date', '<=', $dateStr)
                ->where('departure_date', '>', $dateStr)
                ->whereIn('status', $activeStatuses)
                ->sum('room_rate');

            $data[] = [
                'label' => $date->format('d M Y'),
                'date' => $dateStr,
                'rooms_booked' => $reservedRooms,
                'rooms_available' => $totalRooms - $reservedRooms,
                'occupancy' => $occupancy,
                'est_revenue' => (float) $revenue,
                'day' => $date->format('D'),
            ];
        }

        return $data;
    }

    protected function buildMonthlyForecast(Carbon $start, Carbon $end, int $totalRooms, array $activeStatuses): array
    {
        $data = [];
        $current = $start->copy()->startOfMonth();

        while ($current->lte($end)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            $daysInMonth = $monthEnd->day;
            $totalRoomNights = $totalRooms * $daysInMonth;

            // Count total room-nights booked in this month
            $bookedNights = 0;
            $totalRevenue = 0;

            $reservations = Reservation::where('arrival_date', '<=', $monthEnd->toDateString())
                ->where('departure_date', '>', $monthStart->toDateString())
                ->whereIn('status', $activeStatuses)
                ->get();

            foreach ($reservations as $res) {
                $overlapStart = max($monthStart->timestamp, Carbon::parse($res->arrival_date)->timestamp);
                $overlapEnd = min($monthEnd->timestamp, Carbon::parse($res->departure_date)->subDay()->timestamp);
                $nights = max(0, (int) ceil(($overlapEnd - $overlapStart) / 86400) + 1);
                $bookedNights += $nights;
                $totalRevenue += $nights * (float) $res->room_rate;
            }

            $occupancy = $totalRoomNights > 0 ? round(($bookedNights / $totalRoomNights) * 100, 1) : 0;

            $data[] = [
                'label' => $current->format('M Y'),
                'date' => $current->format('Y-m'),
                'rooms_booked' => $bookedNights,
                'rooms_available' => $totalRoomNights - $bookedNights,
                'occupancy' => $occupancy,
                'est_revenue' => $totalRevenue,
                'day' => $daysInMonth . ' days',
            ];

            $current->addMonth();
        }

        return $data;
    }

    protected function buildAnnualForecast(Carbon $start, Carbon $end, int $totalRooms, array $activeStatuses): array
    {
        $data = [];
        $currentYear = $start->copy()->startOfYear();

        while ($currentYear->lte($end)) {
            $yearStart = $currentYear->copy()->startOfYear();
            $yearEnd = $currentYear->copy()->endOfYear();
            $daysInYear = $currentYear->isLeapYear() ? 366 : 365;
            $totalRoomNights = $totalRooms * $daysInYear;

            $reservations = Reservation::where('arrival_date', '<=', $yearEnd->toDateString())
                ->where('departure_date', '>', $yearStart->toDateString())
                ->whereIn('status', $activeStatuses)
                ->get();

            $bookedNights = 0;
            $totalRevenue = 0;

            foreach ($reservations as $res) {
                $overlapStart = max($yearStart->timestamp, Carbon::parse($res->arrival_date)->timestamp);
                $overlapEnd = min($yearEnd->timestamp, Carbon::parse($res->departure_date)->subDay()->timestamp);
                $nights = max(0, (int) ceil(($overlapEnd - $overlapStart) / 86400) + 1);
                $bookedNights += $nights;
                $totalRevenue += $nights * (float) $res->room_rate;
            }

            $occupancy = $totalRoomNights > 0 ? round(($bookedNights / $totalRoomNights) * 100, 1) : 0;

            $data[] = [
                'label' => $currentYear->format('Y'),
                'date' => $currentYear->format('Y'),
                'rooms_booked' => $bookedNights,
                'rooms_available' => $totalRoomNights - $bookedNights,
                'occupancy' => $occupancy,
                'est_revenue' => $totalRevenue,
                'day' => $daysInYear . ' days',
            ];

            $currentYear->addYear();
        }

        return $data;
    }

    protected function getReportTitle(): string
    {
        return 'Occupancy Forecast';
    }

    protected function getExportData(): array
    {
        $rows = array_map(fn ($row) => [
            $row['label'],
            $row['day'],
            (string) $row['rooms_booked'],
            (string) max(0, $row['rooms_available']),
            $row['occupancy'] . '%',
            'Rp ' . number_format($row['est_revenue'], 0, ',', '.'),
        ], $this->forecastData);

        return [
            'headers' => ['Period', 'Duration', 'Booked', 'Available', 'Occupancy', 'Est. Revenue'],
            'rows' => $rows,
            'subtitle' => ucfirst($this->forecastMode) . ' forecast: ' . $this->startDate . ' — ' . $this->endDate,
            'summary' => [
                ['label' => 'Total Rooms', 'value' => (string) ($this->summaryData['total_rooms'] ?? 0)],
                ['label' => 'Avg Occupancy', 'value' => ($this->summaryData['avg_occupancy'] ?? 0) . '%'],
                ['label' => 'Peak', 'value' => ($this->summaryData['max_occupancy'] ?? 0) . '%'],
            ],
        ];
    }
}
