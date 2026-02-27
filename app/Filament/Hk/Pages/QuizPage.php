<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class QuizPage extends Page
{
    protected string $view = 'filament.shared.pages.quiz';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::QuestionMarkCircle;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 91;

    protected static ?string $navigationLabel = 'Quizzes';

    protected static ?string $title = 'Quizzes';

    protected static ?string $slug = 'quizzes';

    public Collection $quizzes;

    public ?int $activeQuizId = null;

    public ?Quiz $activeQuiz = null;

    public Collection $questions;

    public array $answers = [];

    public ?array $result = null;

    public Collection $attemptHistory;

    public function mount(): void
    {
        $this->questions = collect();
        $this->attemptHistory = collect();
        $this->loadQuizzes();
    }

    public function loadQuizzes(): void
    {
        $this->quizzes = Quiz::query()
            ->where('module', 'hk')
            ->where('is_active', true)
            ->withCount('questions')
            ->get()
            ->map(function (Quiz $quiz) {
                $lastAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('user_id', auth()->id())
                    ->latest('completed_at')
                    ->first();

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'questions_count' => $quiz->questions_count,
                    'passing_score' => $quiz->passing_score,
                    'time_limit' => $quiz->time_limit_minutes,
                    'last_score' => $lastAttempt?->score,
                    'last_passed' => $lastAttempt?->passed,
                ];
            });
    }

    public function startQuiz(int $quizId): void
    {
        $quiz = Quiz::with('questions')->find($quizId);
        if (! $quiz) {
            return;
        }

        $this->activeQuizId = $quizId;
        $this->activeQuiz = $quiz;
        $this->questions = $quiz->questions;
        $this->answers = [];
        $this->result = null;
    }

    public function submitQuiz(): void
    {
        if (! $this->activeQuiz) {
            return;
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $this->activeQuizId,
            'user_id' => auth()->id(),
            'answers' => $this->answers,
            'score' => 0,
            'passed' => false,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $score = $attempt->calculateScore();
        $passed = $score >= $this->activeQuiz->passing_score;

        $attempt->update(['score' => $score, 'passed' => $passed]);

        $this->result = [
            'score' => $score,
            'passed' => $passed,
            'passing_score' => $this->activeQuiz->passing_score,
            'correct_answers' => $this->getCorrectAnswers(),
        ];

        Notification::make()
            ->title($passed ? 'Quiz Passed!' : 'Quiz Not Passed')
            ->body("Score: {$score}%")
            ->color($passed ? 'success' : 'danger')
            ->send();
    }

    public function closeQuiz(): void
    {
        $this->activeQuizId = null;
        $this->activeQuiz = null;
        $this->questions = collect();
        $this->answers = [];
        $this->result = null;
        $this->loadQuizzes();
    }

    public function viewHistory(int $quizId): void
    {
        $this->attemptHistory = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', auth()->id())
            ->orderByDesc('completed_at')
            ->get();
    }

    protected function getCorrectAnswers(): array
    {
        $correct = [];
        foreach ($this->questions as $question) {
            $correct[$question->id] = $question->correct_answer;
        }

        return $correct;
    }
}
