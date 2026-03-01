<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomStatusLog extends Model
{
    protected $fillable = [
        'room_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => RoomStatus::class,
            'new_status' => RoomStatus::class,
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
