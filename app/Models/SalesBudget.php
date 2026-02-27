<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesBudget extends Model
{
    protected $fillable = [
        'year',
        'month',
        'segment_id',
        'room_nights',
        'revenue',
        'average_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'room_nights' => 'integer',
            'revenue' => 'decimal:2',
            'average_rate' => 'decimal:2',
        ];
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }
}
