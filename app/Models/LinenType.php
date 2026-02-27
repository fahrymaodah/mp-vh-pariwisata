<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinenType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'par_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'par_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LinenTransaction::class);
    }
}
