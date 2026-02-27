<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinenTransaction extends Model
{
    protected $fillable = [
        'linen_type_id',
        'room_id',
        'transaction_date',
        'quantity_sent',
        'quantity_received',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity_sent' => 'integer',
            'quantity_received' => 'integer',
        ];
    }

    public function linenType(): BelongsTo
    {
        return $this->belongsTo(LinenType::class);
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
