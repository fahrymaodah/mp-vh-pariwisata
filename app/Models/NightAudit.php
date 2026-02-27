<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NightAuditStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAudit extends Model
{
    protected $fillable = [
        'audit_date',
        'status',
        'checklist',
        'total_revenue',
        'total_rooms_occupied',
        'total_rooms_available',
        'occupancy_rate',
        'started_at',
        'completed_at',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'status' => NightAuditStatus::class,
            'checklist' => 'array',
            'total_revenue' => 'decimal:2',
            'total_rooms_occupied' => 'integer',
            'total_rooms_available' => 'integer',
            'occupancy_rate' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
