<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\QuizAttempt;
use App\Models\ScenarioAssignment;
use App\Models\TutorialProgress;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class StudentProgressPage extends Page
{
    protected string $view = 'filament.admin.pages.student-progress';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBar;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 33;

    protected static ?string $navigationLabel = 'Student Progress';

    protected static ?string $title = 'Student Progress';

    protected static ?string $slug = 'student-progress';

    public Collection $students;

    public ?int $selectedStudentId = null;

    public array $studentStats = [];

    public function mount(): void
    {
        $this->students = User::where('role', UserRole::Student)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
        $this->loadStudentStats();
    }

    public function loadStudentStats(): void
    {
        if (! $this->selectedStudentId) {
            $this->studentStats = [];

            return;
        }

        $userId = $this->selectedStudentId;

        // Scenario assignments
        $scenarios = ScenarioAssignment::where('user_id', $userId)->get();
        $scenariosCompleted = $scenarios->where('status', 'completed')->count();
        $scenariosTotal = $scenarios->count();
        $avgScenarioScore = $scenarios->where('status', 'completed')->avg('score') ?? 0;

        // Quiz attempts
        $quizAttempts = QuizAttempt::where('user_id', $userId)->get();
        $quizzesPassed = $quizAttempts->where('passed', true)->count();
        $quizzesTotal = $quizAttempts->count();
        $avgQuizScore = $quizAttempts->avg('score') ?? 0;

        // Tutorial progress
        $tutorials = TutorialProgress::where('user_id', $userId)->get();
        $tutorialsCompleted = $tutorials->where('is_completed', true)->count();
        $tutorialsTotal = $tutorials->count();

        // Activity logs (last 7 days)
        $recentActivities = ActivityLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Module activity breakdown
        $moduleBreakdown = ActivityLog::where('user_id', $userId)
            ->selectRaw('module, COUNT(*) as count')
            ->groupBy('module')
            ->pluck('count', 'module')
            ->toArray();

        $this->studentStats = [
            'scenarios_completed' => $scenariosCompleted,
            'scenarios_total' => $scenariosTotal,
            'avg_scenario_score' => round($avgScenarioScore, 1),
            'quizzes_passed' => $quizzesPassed,
            'quizzes_total' => $quizzesTotal,
            'avg_quiz_score' => round($avgQuizScore, 1),
            'tutorials_completed' => $tutorialsCompleted,
            'tutorials_total' => $tutorialsTotal,
            'recent_activities' => $recentActivities,
            'module_breakdown' => $moduleBreakdown,
        ];
    }
}
