<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixCostArticle extends Model
{
    protected $fillable = [
        'reservation_id',
        'department_id',
        'article_id',
        'qty',
        'price',
        'posting_type',
        'total_posting',
        'start_from',
    ];

    protected function casts(): array
    {
        return [
            'posting_type' => PostingType::class,
            'qty' => 'integer',
            'price' => 'decimal:2',
            'total_posting' => 'integer',
            'start_from' => 'date',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
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
