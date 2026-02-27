<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesSchedule extends Model
{
    protected $fillable = [
        'sales_person_id',
        'guest_id',
        'opportunity_id',
        'schedule_date',
        'start_time',
        'end_time',
        'subject',
        'location',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class, 'opportunity_id');
    }
}
