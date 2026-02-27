<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinenTransaction extends Model
{
    protected $fillable = [
        'linen_type_id',
        'type',
        'qty',
        'date',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'qty' => 'integer',
        ];
    }

    public function linenType(): BelongsTo
    {
        return $this->belongsTo(LinenType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
