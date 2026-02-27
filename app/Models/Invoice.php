<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'reservation_id',
        'guest_id',
        'room_id',
        'type',
        'status',
        'bill_address',
        'comments',
        'total_sales',
        'total_payment',
        'balance',
        'printed_at',
        'closed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'total_sales' => 'decimal:2',
            'total_payment' => 'decimal:2',
            'balance' => 'decimal:2',
            'printed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Boot ─────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_no)) {
                $lastInvoice = static::orderByDesc('id')->first();
                $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
                $invoice->invoice_no = 'INV' . str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);
            }
        });
    }
}
