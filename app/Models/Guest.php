<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\GuestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_no',
        'type',
        'name',
        'first_name',
        'title',
        'company_title',
        'address',
        'city',
        'zip',
        'country',
        'nationality',
        'birth_date',
        'birth_place',
        'sex',
        'id_card_no',
        'phone',
        'fax',
        'email',
        'credit_limit',
        'master_company_id',
        'main_segment_id',
        'sales_user_id',
        'price_code',
        'discount',
        'source_booking',
        'payment_terms',
        'comments',
        'photo_path',
        'is_vip',
        'is_blacklisted',
        'expired_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => GuestType::class,
            'sex' => Gender::class,
            'birth_date' => 'date',
            'expired_date' => 'date',
            'credit_limit' => 'decimal:2',
            'discount' => 'decimal:2',
            'is_vip' => 'boolean',
            'is_blacklisted' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function masterCompany(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'master_company_id');
    }

    public function mainSegment(): BelongsTo
    {
        return $this->belongsTo(Segment::class, 'main_segment_id');
    }

    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'guest_segment')
            ->withPivot('is_main');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(GuestContact::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GuestMembership::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeIndividual($query)
    {
        return $query->where('type', GuestType::Individual);
    }

    public function scopeCompany($query)
    {
        return $query->where('type', GuestType::Company);
    }

    public function scopeTravelAgent($query)
    {
        return $query->where('type', GuestType::TravelAgent);
    }

    // ── Accessors ────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        if ($this->type === GuestType::Individual) {
            return trim("{$this->title} {$this->first_name} {$this->name}");
        }

        return trim("{$this->company_title} {$this->name}");
    }

    // ── Boot ─────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Guest $guest) {
            if (empty($guest->guest_no)) {
                $prefix = match ($guest->type) {
                    GuestType::Individual => 'I',
                    GuestType::Company => 'C',
                    GuestType::TravelAgent => 'T',
                    default => 'I',
                };
                $lastGuest = static::where('guest_no', 'like', "{$prefix}%")
                    ->orderByDesc('guest_no')
                    ->first();
                $nextNumber = $lastGuest
                    ? ((int) substr($lastGuest->guest_no, 1)) + 1
                    : 1;
                $guest->guest_no = $prefix . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
