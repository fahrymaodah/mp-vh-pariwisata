<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'article_id',
        'department_id',
        'description',
        'qty',
        'rate',
        'amount',
        'tax_amount',
        'service_amount',
        'is_cancelled',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
        'transferred_from_invoice_id',
        'transferred_to_invoice_id',
        'posting_date',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_amount' => 'decimal:2',
            'is_cancelled' => 'boolean',
            'cancelled_at' => 'datetime',
            'posting_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
