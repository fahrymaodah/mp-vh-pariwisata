<?php

declare(strict_types=1);

namespace App\Filament\Sales\Pages;

use App\Models\SalesSchedule;
use App\Models\SalesTask;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class SalesCalendar extends Page
{
    protected string $view = 'filament.sales.pages.sales-calendar';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CalendarDays;
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Calendar';
    protected static ?string $title = 'Sales Calendar';
    protected static ?int $navigationSort = 4;

    public int $currentMonth;
    public int $currentYear;

    public function mount(): void
    {
        $this->currentMonth = (int) now()->format('m');
        $this->currentYear = (int) now()->format('Y');
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = (int) $date->format('m');
        $this->currentYear = (int) $date->format('Y');
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = (int) $date->format('m');
        $this->currentYear = (int) $date->format('Y');
    }

    public function getCalendarData(): array
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get schedules for this month
        $schedules = SalesSchedule::with(['opportunity', 'activityCode'])
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_time')
            ->get();

        // Get tasks for this month
        $tasks = SalesTask::where('user_id', auth()->id())
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->where('is_completed', false)
            ->get();

        // Build calendar grid
        $calendar = [];
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0=Sunday
        $daysInMonth = $startOfMonth->daysInMonth;

        $week = [];
        // Fill leading empty days
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $week[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $dateStr = $date->format('Y-m-d');

            $daySchedules = $schedules->filter(fn ($s) => $s->date->format('Y-m-d') === $dateStr);
            $dayTasks = $tasks->filter(fn ($t) => $t->due_date && $t->due_date->format('Y-m-d') === $dateStr);

            $week[] = [
                'day' => $day,
                'date' => $dateStr,
                'is_today' => $date->isToday(),
                'schedules' => $daySchedules->map(fn ($s) => [
                    'time' => substr((string) $s->start_time, 0, 5),
                    'label' => $s->regarding ? str()->limit($s->regarding, 20) : ($s->activityCode?->description ?? 'Schedule'),
                ])->values()->toArray(),
                'tasks' => $dayTasks->map(fn ($t) => [
                    'title' => str()->limit($t->title, 20),
                ])->values()->toArray(),
            ];

            if (count($week) === 7) {
                $calendar[] = $week;
                $week = [];
            }
        }

        // Fill trailing empty days
        if (count($week) > 0) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $calendar[] = $week;
        }

        return [
            'monthName' => $startOfMonth->format('F Y'),
            'weeks' => $calendar,
            'dayHeaders' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        ];
    }
}
