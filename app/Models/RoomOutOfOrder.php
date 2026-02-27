<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OooType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomOutOfOrder extends Model
{
    protected $fillable = [
        'room_id',
        'type',
        'reason',
        'from_date',
        'until_date',
        'department',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => OooType::class,
            'from_date' => 'date',
            'until_date' => 'date',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
