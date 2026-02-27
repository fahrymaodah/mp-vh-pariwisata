<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOpportunity extends Model
{
    protected $fillable = [
        'opportunity_no',
        'guest_id',
        'sales_stage_id',
        'sales_person_id',
        'product_id',
        'referral_source_id',
        'title',
        'description',
        'estimated_value',
        'expected_close_date',
        'actual_close_date',
        'status',
        'won_reason_id',
        'lost_reason_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'actual_close_date' => 'date',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function salesStage(): BelongsTo
    {
        return $this->belongsTo(SalesStage::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SalesProduct::class, 'product_id');
    }

    public function referralSource(): BelongsTo
    {
        return $this->belongsTo(SalesReferralSource::class, 'referral_source_id');
    }

    public function wonReason(): BelongsTo
    {
        return $this->belongsTo(SalesReason::class, 'won_reason_id');
    }

    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(SalesReason::class, 'lost_reason_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SalesActivity::class, 'opportunity_id');
    }
}
