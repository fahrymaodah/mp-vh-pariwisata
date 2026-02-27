<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Models\NightAudit;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SystemDateManagement extends Page
{
    protected string $view = 'filament.fo.pages.system-date-management';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CalendarDays;

    protected static string | UnitEnum | null $navigationGroup = 'Night Audit';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'System Date';

    protected static ?string $title = 'System Date Management';

    protected static ?string $slug = 'system-date-management';

    public ?string $currentDate = null;

    public ?string $lastAudit = null;

    public ?string $newDate = null;

    public function mount(): void
    {
        $this->refreshDates();
    }

    public function refreshDates(): void
    {
        $systemDate = SystemDate::current();
        $this->currentDate = $systemDate?->current_date?->toDateString() ?? now()->toDateString();
        $this->lastAudit = $systemDate?->last_night_audit?->toDateString();
        $this->newDate = null;
    }

    /**
     * Advance system date by 1 day (standard operation).
     */
    public function advanceOneDay(): void
    {
        $systemDate = SystemDate::current();
        if (! $systemDate) {
            Notification::make()->title('System date not found.')->danger()->send();

            return;
        }

        $nextDate = \Carbon\Carbon::parse($systemDate->current_date)->addDay();

        $systemDate->update([
            'current_date' => $nextDate->toDateString(),
        ]);

        Notification::make()
            ->title('Date Advanced')
            ->body("System date changed to {$nextDate->format('d M Y')}")
            ->success()
            ->send();

        $this->refreshDates();
    }

    /**
     * Go back by 1 day (training reset).
     */
    public function goBackOneDay(): void
    {
        $systemDate = SystemDate::current();
        if (! $systemDate) {
            Notification::make()->title('System date not found.')->danger()->send();

            return;
        }

        $prevDate = \Carbon\Carbon::parse($systemDate->current_date)->subDay();

        $systemDate->update([
            'current_date' => $prevDate->toDateString(),
        ]);

        Notification::make()
            ->title('Date Reversed')
            ->body("System date changed to {$prevDate->format('d M Y')}")
            ->warning()
            ->send();

        $this->refreshDates();
    }

    /**
     * Set system date to a specific date.
     */
    public function setSpecificDate(): void
    {
        if (! $this->newDate) {
            Notification::make()->title('Please select a date.')->warning()->send();

            return;
        }

        $systemDate = SystemDate::current();
        if (! $systemDate) {
            Notification::make()->title('System date not found.')->danger()->send();

            return;
        }

        $systemDate->update([
            'current_date' => $this->newDate,
        ]);

        $formatted = \Carbon\Carbon::parse($this->newDate)->format('d M Y');

        Notification::make()
            ->title('Date Updated')
            ->body("System date set to {$formatted}")
            ->success()
            ->send();

        $this->refreshDates();
    }

    /**
     * Reset system date to today (real date).
     */
    public function resetToToday(): void
    {
        $systemDate = SystemDate::current();
        if (! $systemDate) {
            Notification::make()->title('System date not found.')->danger()->send();

            return;
        }

        $today = now()->toDateString();
        $systemDate->update([
            'current_date' => $today,
        ]);

        Notification::make()
            ->title('Date Reset')
            ->body('System date reset to today: ' . now()->format('d M Y'))
            ->success()
            ->send();

        $this->refreshDates();
    }

    public function getRecentAudits(): array
    {
        return NightAudit::latest('audit_date')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'date' => $a->audit_date->format('d M Y'),
                'status' => $a->status->label(),
                'color' => $a->status->color(),
            ])
            ->toArray();
    }
}
