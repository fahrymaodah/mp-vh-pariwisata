<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOpportunity extends Model
{
    protected $fillable = [
        'sales_activity_id',
        'guest_id',
        'contact_name',
        'prospect_name',
        'stage_id',
        'product_id',
        'status',
        'target_amount',
        'probability',
        'finish_date',
        'reason_id',
        'source_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'probability' => 'integer',
            'finish_date' => 'date',
        ];
    }

    public function salesActivity(): BelongsTo
    {
        return $this->belongsTo(SalesActivity::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(SalesStage::class, 'stage_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SalesProduct::class, 'product_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(SalesReason::class, 'reason_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SalesReferralSource::class, 'source_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SalesSchedule::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SalesTask::class);
    }
}
