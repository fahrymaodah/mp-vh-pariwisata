<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'phone',
        'fax',
        'email',
        'website',
        'checkout_time',
        'currency_code',
        'tax_percentage',
        'service_percentage',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
            'service_percentage' => 'decimal:2',
        ];
    }
}
