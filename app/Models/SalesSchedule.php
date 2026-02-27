<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesSchedule extends Model
{
    protected $fillable = [
        'sales_opportunity_id',
        'activity_code_id',
        'date',
        'start_time',
        'end_time',
        'priority',
        'scheduled_with',
        'regarding',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'priority' => 'integer',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class, 'sales_opportunity_id');
    }

    public function activityCode(): BelongsTo
    {
        return $this->belongsTo(ActivityCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
