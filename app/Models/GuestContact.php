<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestContact extends Model
{
    protected $fillable = [
        'guest_id',
        'name',
        'first_name',
        'title',
        'birth_date',
        'birth_place',
        'department',
        'function',
        'extension',
        'email',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_main' => 'boolean',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->name}");
    }
}
