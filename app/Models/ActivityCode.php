<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCode extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];

    public function salesSchedules(): HasMany
    {
        return $this->hasMany(SalesSchedule::class);
    }
}
