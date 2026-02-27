<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_category_id',
        'floor',
        'status',
        'is_active',
        'is_smoking',
        'overlook',
        'connecting_room',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'floor' => 'integer',
            'is_active' => 'boolean',
            'is_smoking' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_category_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function outOfOrders(): HasMany
    {
        return $this->hasMany(RoomOutOfOrder::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RoomStatusLog::class);
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', [
            RoomStatus::VacantClean,
            RoomStatus::VacantDirty,
            RoomStatus::VacantCleanUnchecked,
        ]);
    }

    public function scopeOccupied($query)
    {
        return $query->whereIn('status', [
            RoomStatus::OccupiedClean,
            RoomStatus::OccupiedDirty,
            RoomStatus::ExpectedDeparture,
        ]);
    }
}
