<?php

declare(strict_types=1);

namespace App\Filament\Telop\Pages;

use App\Models\Tutorial;
use App\Models\TutorialProgress;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class TutorialListPage extends Page
{
    protected string $view = 'filament.shared.pages.tutorial-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BookOpen;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'Tutorials';

    protected static ?string $title = 'Available Tutorials';

    protected static ?string $slug = 'tutorials';

    public Collection $tutorials;

    public ?int $activeTutorialId = null;

    public array $activeSteps = [];

    public int $currentStep = 0;

    public function mount(): void
    {
        $this->loadTutorials();
    }

    public function loadTutorials(): void
    {
        $this->tutorials = Tutorial::query()
            ->where('module', 'telop')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Tutorial $tutorial) {
                $progress = TutorialProgress::where('tutorial_id', $tutorial->id)
                    ->where('user_id', auth()->id())
                    ->first();

                return [
                    'id' => $tutorial->id,
                    'title' => $tutorial->title,
                    'description' => $tutorial->description,
                    'steps_count' => is_array($tutorial->steps) ? count($tutorial->steps) : 0,
                    'current_step' => $progress?->current_step ?? 0,
                    'is_completed' => $progress?->is_completed ?? false,
                ];
            });
    }

    public function startTutorial(int $tutorialId): void
    {
        $tutorial = Tutorial::find($tutorialId);
        if (! $tutorial) {
            return;
        }

        $this->activeTutorialId = $tutorialId;
        $this->activeSteps = $tutorial->steps ?? [];
        $this->currentStep = 0;

        TutorialProgress::updateOrCreate(
            ['tutorial_id' => $tutorialId, 'user_id' => auth()->id()],
            ['current_step' => 0, 'is_completed' => false]
        );
    }

    public function nextStep(): void
    {
        if ($this->currentStep < count($this->activeSteps) - 1) {
            $this->currentStep++;
            $this->updateProgress();
        } else {
            $this->completeTutorial();
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
            $this->updateProgress();
        }
    }

    public function closeTutorial(): void
    {
        $this->activeTutorialId = null;
        $this->activeSteps = [];
        $this->currentStep = 0;
        $this->loadTutorials();
    }

    protected function updateProgress(): void
    {
        if (! $this->activeTutorialId) {
            return;
        }

        TutorialProgress::updateOrCreate(
            ['tutorial_id' => $this->activeTutorialId, 'user_id' => auth()->id()],
            ['current_step' => $this->currentStep]
        );
    }

    protected function completeTutorial(): void
    {
        if (! $this->activeTutorialId) {
            return;
        }

        TutorialProgress::updateOrCreate(
            ['tutorial_id' => $this->activeTutorialId, 'user_id' => auth()->id()],
            ['current_step' => $this->currentStep, 'is_completed' => true, 'completed_at' => now()]
        );

        Notification::make()
            ->title('Tutorial completed!')
            ->success()
            ->send();

        $this->closeTutorial();
    }
}
