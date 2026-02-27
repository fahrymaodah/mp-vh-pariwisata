<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_no',
        'name',
        'department_id',
        'type',
        'default_price',
        'tax_inclusive',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            'default_price' => 'decimal:2',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSales($query)
    {
        return $query->where('type', ArticleType::Sales);
    }

    public function scopePayment($query)
    {
        return $query->where('type', ArticleType::Payment);
    }
}
