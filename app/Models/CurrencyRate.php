<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'code',
        'description',
        'purchase_rate',
        'sales_rate',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'purchase_rate' => 'decimal:6',
            'sales_rate' => 'decimal:6',
            'unit' => 'integer',
        ];
    }
}
