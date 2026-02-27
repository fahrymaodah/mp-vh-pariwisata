<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesStage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'probability',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }
}
