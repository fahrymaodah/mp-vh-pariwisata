<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScenarioAssignment extends Model
{
    protected $fillable = [
        'scenario_id',
        'user_id',
        'status',
        'completed_objectives',
        'score',
        'instructor_notes',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_objectives' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────

    public function markInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markCompleted(?int $score = null): void
    {
        $this->update([
            'status' => 'completed',
            'score' => $score,
            'completed_at' => now(),
        ]);
    }
}
