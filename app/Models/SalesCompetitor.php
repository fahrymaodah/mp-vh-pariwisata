<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCompetitor extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'star_rating',
        'total_rooms',
        'strengths',
        'weaknesses',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'star_rating' => 'integer',
            'total_rooms' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
