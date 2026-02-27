<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesActivity extends Model
{
    protected $fillable = [
        'opportunity_id',
        'activity_code_id',
        'sales_person_id',
        'guest_id',
        'activity_date',
        'subject',
        'description',
        'result',
        'next_action',
        'next_action_date',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
            'next_action_date' => 'date',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class, 'opportunity_id');
    }

    public function activityCode(): BelongsTo
    {
        return $this->belongsTo(ActivityCode::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
