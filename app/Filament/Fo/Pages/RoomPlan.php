<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\SystemDate;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RoomPlan extends Page
{
    protected static ?string $title = 'Room Plan';

    protected static ?string $navigationLabel = 'Room Plan';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ViewColumns;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.fo.pages.room-plan';

    public ?string $startDate = null;

    public ?int $categoryId = null;

    public int $daysToShow = 14;

    public array $dates = [];

    public array $rooms = [];

    public array $reservationSlots = [];

    public function mount(): void
    {
        $this->startDate = SystemDate::today();
        $this->loadData();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate')
                ->label('Start Date')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
            Select::make('categoryId')
                ->label('Room Category')
                ->options(RoomCategory::pluck('name', 'id'))
                ->placeholder('All Categories')
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
            Select::make('daysToShow')
                ->label('Days')
                ->options([
                    7 => '7 days',
                    14 => '14 days',
                    21 => '21 days',
                    30 => '30 days',
                ])
                ->default(14)
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
        ]);
    }

    public function loadData(): void
    {
        if (! $this->startDate) {
            return;
        }

        $start = Carbon::parse($this->startDate);
        $end = $start->copy()->addDays($this->daysToShow - 1);

        // Build date headers
        $this->dates = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $this->dates[] = [
                'date' => $current->toDateString(),
                'label' => $current->format('d'),
                'day' => $current->format('D'),
                'month' => $current->format('M'),
                'isToday' => $current->toDateString() === SystemDate::today(),
                'isWeekend' => $current->isWeekend(),
            ];
            $current->addDay();
        }

        // Get rooms
        $roomQuery = Room::query()->where('is_active', true)->with('category');
        if ($this->categoryId) {
            $roomQuery->where('room_category_id', $this->categoryId);
        }
        $roomCollection = $roomQuery->orderBy('room_number')->get();

        $this->rooms = $roomCollection->map(fn (Room $room) => [
            'id' => $room->id,
            'number' => $room->room_number,
            'category' => $room->category->code ?? '-',
            'status' => $room->status->value,
            'statusColor' => $room->status->color(),
        ])->toArray();

        // Get reservations in range
        $reservations = Reservation::query()
            ->with('guest')
            ->where('arrival_date', '<=', $end->toDateString())
            ->where('departure_date', '>', $start->toDateString())
            ->whereNotNull('room_id')
            ->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
            ])
            ->get();

        // Build slot map: room_id => [date => reservation_info]
        $this->reservationSlots = [];
        foreach ($reservations as $res) {
            $resStart = Carbon::parse($res->arrival_date);
            $resEnd = Carbon::parse($res->departure_date);

            // Clip to visible range
            $slotStart = $resStart->lt($start) ? $start->copy() : $resStart->copy();
            $slotEnd = $resEnd->gt($end) ? $end->copy()->addDay() : $resEnd->copy();

            $dateCursor = $slotStart->copy();
            while ($dateCursor->lt($slotEnd)) {
                $dateStr = $dateCursor->toDateString();
                $this->reservationSlots[$res->room_id][$dateStr] = [
                    'reservation_id' => $res->id,
                    'reservation_no' => $res->reservation_no,
                    'guest_name' => $res->guest?->full_name ?? 'N/A',
                    'arrival' => $res->arrival_date->toDateString(),
                    'departure' => $res->departure_date->toDateString(),
                    'status' => $res->status->value,
                    'color' => $this->getReservationColor($res),
                    'isFirstDay' => $dateCursor->toDateString() === $resStart->toDateString(),
                    'isCheckedIn' => $res->status === ReservationStatus::CheckedIn,
                ];
                $dateCursor->addDay();
            }
        }
    }

    private function getReservationColor(Reservation $reservation): string
    {
        return match ($reservation->status) {
            ReservationStatus::CheckedIn => 'bg-green-500 dark:bg-green-600',
            ReservationStatus::Guaranteed => 'bg-blue-500 dark:bg-blue-600',
            ReservationStatus::Confirmed => 'bg-blue-400 dark:bg-blue-500',
            ReservationStatus::SixPm => 'bg-yellow-500 dark:bg-yellow-600',
            ReservationStatus::Tentative => 'bg-gray-400 dark:bg-gray-500',
            ReservationStatus::WaitingList => 'bg-orange-400 dark:bg-orange-500',
            ReservationStatus::OralConfirmed => 'bg-teal-400 dark:bg-teal-500',
            ReservationStatus::CheckedOut => 'bg-gray-300 dark:bg-gray-600',
            default => 'bg-gray-300 dark:bg-gray-600',
        };
    }
}
