<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostAndFound extends Model
{
    protected $table = 'lost_and_founds';

    protected $fillable = [
        'item_description',
        'found_location',
        'found_date',
        'found_by',
        'room_id',
        'guest_id',
        'status',
        'claimed_by',
        'claimed_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'found_date' => 'date',
            'claimed_date' => 'date',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function foundByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'found_by');
    }
}
