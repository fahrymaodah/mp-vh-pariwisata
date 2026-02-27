<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scenario extends Model
{
    protected $fillable = [
        'title',
        'description',
        'module',
        'difficulty',
        'instructions',
        'objectives',
        'initial_data',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'objectives' => 'array',
            'initial_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScenarioAssignment::class);
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}
