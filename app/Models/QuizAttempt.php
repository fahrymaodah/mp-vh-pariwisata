<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'answers',
        'score',
        'passed',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────

    public function calculateScore(): int
    {
        if (empty($this->answers)) {
            return 0;
        }

        $questions = $this->quiz->questions;
        $correct = 0;
        $total = $questions->count();

        foreach ($questions as $question) {
            $answer = $this->answers[$question->id] ?? null;
            if ($answer !== null && (string) $answer === (string) $question->correct_answer) {
                $correct++;
            }
        }

        return $total > 0 ? (int) round(($correct / $total) * 100) : 0;
    }
}
