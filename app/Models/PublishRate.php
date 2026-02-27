<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishRate extends Model
{
    protected $fillable = [
        'room_category_id',
        'arrangement_id',
        'day_of_week',
        'start_date',
        'end_date',
        'rate_single',
        'rate_double',
        'rate_triple',
        'rate_quad',
        'extra_child1',
        'extra_child2',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'rate_single' => 'decimal:2',
            'rate_double' => 'decimal:2',
            'rate_triple' => 'decimal:2',
            'rate_quad' => 'decimal:2',
            'extra_child1' => 'decimal:2',
            'extra_child2' => 'decimal:2',
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
