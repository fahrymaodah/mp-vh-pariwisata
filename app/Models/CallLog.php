<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'reservation_id',
        'room_id',
        'extension_no',
        'dialed_number',
        'call_date',
        'call_time',
        'duration',
        'call_type',
        'rate_amount',
        'is_posted',
        'posted_to_bill',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'call_date' => 'date',
            'duration' => 'integer',
            'rate_amount' => 'decimal:2',
            'is_posted' => 'boolean',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
