<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCardType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GuestMembership::class);
    }
}
