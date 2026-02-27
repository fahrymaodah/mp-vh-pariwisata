<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrangementLine extends Model
{
    protected $fillable = [
        'arrangement_id',
        'department_id',
        'article_id',
        'amount',
        'posting_type',
        'total_posting',
        'included_in_room_rate',
        'qty_always_one',
        'guest_type',
    ];

    protected function casts(): array
    {
        return [
            'posting_type' => PostingType::class,
            'amount' => 'decimal:2',
            'total_posting' => 'integer',
            'included_in_room_rate' => 'boolean',
            'qty_always_one' => 'boolean',
        ];
    }

    public function arrangement(): BelongsTo
    {
        return $this->belongsTo(Arrangement::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
