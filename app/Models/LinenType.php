<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinenType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(LinenTransaction::class);
    }
}
