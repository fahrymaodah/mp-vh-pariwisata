<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\NightAuditStatus;
use App\Enums\UserRole;
use App\Models\NightAudit;
use App\Models\SystemDate;
use App\Services\NightAuditService;
use BackedEnum;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class NightAuditPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::nightAuditRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.night-audit';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Moon;

    protected static string | UnitEnum | null $navigationGroup = 'Night Audit';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Night Audit';

    protected static ?string $title = 'Night Audit';

    protected static ?string $slug = 'night-audit';

    public array $checklist = [];

    public bool $auditCompleted = false;

    public ?string $auditStatus = null;

    public ?NightAudit $lastAudit = null;

    public function mount(): void
    {
        $this->refreshChecklist();
        $this->loadLastAudit();
    }

    public function refreshChecklist(): void
    {
        $this->checklist = app(NightAuditService::class)->runChecklist();
    }

    public function loadLastAudit(): void
    {
        $today = SystemDate::today();
        $audit = NightAudit::where('audit_date', $today)->first();

        if ($audit) {
            $this->auditStatus = $audit->status->value;
            $this->auditCompleted = $audit->status === NightAuditStatus::Completed;
            $this->lastAudit = $audit;
        } else {
            $this->auditStatus = null;
            $this->auditCompleted = false;
            $this->lastAudit = null;
        }
    }

    public function runMidnightProgram(): void
    {
        try {
            $audit = app(NightAuditService::class)->runMidnightProgram();

            $this->auditCompleted = true;
            $this->auditStatus = 'completed';
            $this->lastAudit = $audit;

            Notification::make()
                ->title('Night Audit Completed')
                ->body($audit->notes)
                ->success()
                ->persistent()
                ->send();

            $this->refreshChecklist();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Night Audit Failed')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->loadLastAudit();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refreshChecklist')
                ->label('Refresh Checklist')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->action(fn () => $this->refreshChecklist()),
        ];
    }

    public function getSystemDate(): string
    {
        return SystemDate::today();
    }

    public function getLastAuditDate(): ?string
    {
        return SystemDate::current()?->last_night_audit?->toDateString();
    }

    public function getChecklistPassCount(): int
    {
        return count(array_filter($this->checklist, fn ($item) => $item['passed']));
    }

    public function getChecklistTotalCount(): int
    {
        return count($this->checklist);
    }

    public function getAllChecklistPassed(): bool
    {
        return $this->getChecklistPassCount() === $this->getChecklistTotalCount() && $this->getChecklistTotalCount() > 0;
    }

    public function getStatistics(): array
    {
        return app(NightAuditService::class)->calculateStatistics();
    }
}
