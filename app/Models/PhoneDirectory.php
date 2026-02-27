<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneDirectory extends Model
{
    protected $fillable = [
        'department',
        'name',
        'address',
        'phone',
        'extension',
        'mobile',
        'fax',
        'email',
        'city',
        'zip',
        'country',
        'contact_person',
    ];
}
