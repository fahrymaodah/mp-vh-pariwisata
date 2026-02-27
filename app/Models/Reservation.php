<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_no',
        'guest_id',
        'group_name',
        'segment_id',
        'reserved_by',
        'source',
        'status',
        'arrival_date',
        'departure_date',
        'nights',
        'adults',
        'children',
        'children_ages',
        'is_complimentary',
        'room_category_id',
        'room_id',
        'room_qty',
        'arrangement_id',
        'room_rate',
        'currency_code',
        'is_fix_rate',
        'bill_instruction',
        'purpose',
        'flight_no',
        'eta',
        'etd',
        'is_pickup',
        'is_dropoff',
        'comments',
        'letter_no',
        'ta_commission',
        'deposit_limit_date',
        'deposit_amount',
        'deposit_paid',
        'deposit2_paid',
        'deposit_balance',
        'is_master_bill',
        'master_bill_receiver',
        'is_incognito',
        'is_day_use',
        'is_room_sharer',
        'parent_reservation_id',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'arrival_date' => 'date',
            'departure_date' => 'date',
            'nights' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'is_complimentary' => 'boolean',
            'room_qty' => 'integer',
            'room_rate' => 'decimal:2',
            'is_fix_rate' => 'boolean',
            'ta_commission' => 'decimal:2',
            'deposit_limit_date' => 'date',
            'deposit_amount' => 'decimal:2',
            'deposit_paid' => 'decimal:2',
            'deposit2_paid' => 'decimal:2',
            'deposit_balance' => 'decimal:2',
            'is_master_bill' => 'boolean',
            'is_incognito' => 'boolean',
            'is_day_use' => 'boolean',
            'is_room_sharer' => 'boolean',
            'is_pickup' => 'boolean',
            'is_dropoff' => 'boolean',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function roomCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function arrangement(): BelongsTo
    {
        return $this->belongsTo(Arrangement::class);
    }

    public function parentReservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'parent_reservation_id');
    }

    public function childReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'parent_reservation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function fixCostArticles(): HasMany
    {
        return $this->hasMany(FixCostArticle::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReservationLog::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GuestMessage::class);
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ReservationStatus::Guaranteed,
            ReservationStatus::SixPm,
            ReservationStatus::OralConfirmed,
            ReservationStatus::Tentative,
            ReservationStatus::WaitingList,
            ReservationStatus::Confirmed,
            ReservationStatus::CheckedIn,
        ]);
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('status', ReservationStatus::CheckedIn);
    }

    public function scopeArrivingToday($query)
    {
        $today = SystemDate::today();
        return $query->where('arrival_date', $today)
            ->whereNotIn('status', [ReservationStatus::Cancelled, ReservationStatus::NoShow, ReservationStatus::CheckedOut]);
    }

    // ── Boot ─────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Reservation $reservation) {
            if (empty($reservation->reservation_no)) {
                $lastReservation = static::orderByDesc('id')->first();
                $nextNumber = $lastReservation ? $lastReservation->id + 1 : 1;
                $reservation->reservation_no = 'R' . str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);
            }

            if (empty($reservation->nights)) {
                $reservation->nights = $reservation->arrival_date->diffInDays($reservation->departure_date);
            }
        });
    }
}
