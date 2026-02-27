<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrangement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'invoice_label',
        'lodging_article_id',
        'arrangement_article_id',
        'min_stay',
        'currency_code',
        'comments',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_stay' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function lodgingArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'lodging_article_id');
    }

    public function arrangementArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'arrangement_article_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ArrangementLine::class);
    }

    public function publishRates(): HasMany
    {
        return $this->hasMany(PublishRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
