<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Services\DataResetService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class DataResetPage extends Page
{
    protected string $view = 'filament.admin.pages.data-reset';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowPath;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 34;

    protected static ?string $navigationLabel = 'Data Reset';

    protected static ?string $title = 'Data Reset';

    protected static ?string $slug = 'data-reset';

    public array $modules = [];

    public array $recordCounts = [];

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $service = new DataResetService();
        $this->modules = $service->getModules();
        $this->recordCounts = [];

        foreach (array_keys($this->modules) as $module) {
            $this->recordCounts[$module] = $service->getModuleRecordCounts($module);
        }
    }

    public function resetModule(string $module): void
    {
        $service = new DataResetService();
        $result = $service->resetModule($module);

        Notification::make()
            ->title("Module '{$this->modules[$module]}' reset successfully")
            ->body(count($result['tables_truncated']) . ' tables truncated, ' . count($result['seeders_run']) . ' seeders run.')
            ->success()
            ->send();

        $this->refreshData();
    }

    public function resetAll(): void
    {
        $service = new DataResetService();
        $service->resetAll();

        Notification::make()
            ->title('Full data reset completed')
            ->body('All module data has been reset and re-seeded.')
            ->success()
            ->send();

        $this->refreshData();
    }
}
