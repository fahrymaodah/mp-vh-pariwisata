<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemDate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'current_date',
        'last_night_audit',
    ];

    protected function casts(): array
    {
        return [
            'current_date' => 'date',
            'last_night_audit' => 'date',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the current system date.
     */
    public static function current(): ?self
    {
        return static::first();
    }

    /**
     * Get the current date value.
     */
    public static function today(): string
    {
        return static::first()?->current_date?->toDateString() ?? now()->toDateString();
    }
}
