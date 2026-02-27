<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'base_rate',
        'max_occupancy',
        'credit_points',
        'bed_setup',
    ];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'max_occupancy' => 'integer',
            'credit_points' => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function publishRates(): HasMany
    {
        return $this->hasMany(PublishRate::class);
    }

    public function contractRates(): HasMany
    {
        return $this->hasMany(ContractRate::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
