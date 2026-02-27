<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestMembership extends Model
{
    protected $fillable = [
        'guest_id',
        'membership_card_type_id',
        'card_number',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(MembershipCardType::class, 'membership_card_type_id');
    }
}
