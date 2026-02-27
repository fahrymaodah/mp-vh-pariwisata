<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesActivity extends Model
{
    protected $fillable = [
        'guest_id',
        'user_id',
        'description',
        'target_amount',
        'priority',
        'competitor',
        'next_action_date',
        'next_action_time',
        'is_finished',
        'finish_date',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'priority' => 'integer',
            'next_action_date' => 'date',
            'is_finished' => 'boolean',
            'finish_date' => 'date',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }
}
