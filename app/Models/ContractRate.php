<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRate extends Model
{
    protected $fillable = [
        'price_code',
        'description',
        'currency_code',
        'room_category_id',
        'arrangement_id',
        'start_date',
        'end_date',
        'day_of_week',
        'adults',
        'room_rate',
        'child1_rate',
        'child2_rate',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'day_of_week' => 'integer',
            'adults' => 'integer',
            'room_rate' => 'decimal:2',
            'child1_rate' => 'decimal:2',
            'child2_rate' => 'decimal:2',
        ];
    }

    public function roomCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class);
    }

    public function arrangement(): BelongsTo
    {
        return $this->belongsTo(Arrangement::class);
    }
}
