<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SegmentBudget extends Model
{
    protected $fillable = [
        'segment_id',
        'date',
        'budget_rooms',
        'budget_persons',
        'budget_lodging',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'budget_rooms' => 'integer',
            'budget_persons' => 'integer',
            'budget_lodging' => 'decimal:2',
        ];
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }
}
