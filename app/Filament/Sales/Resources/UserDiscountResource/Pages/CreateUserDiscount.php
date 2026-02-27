<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\UserDiscountResource\Pages;

use App\Filament\Sales\Resources\UserDiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserDiscount extends CreateRecord
{
    protected static string $resource = UserDiscountResource::class;
}
