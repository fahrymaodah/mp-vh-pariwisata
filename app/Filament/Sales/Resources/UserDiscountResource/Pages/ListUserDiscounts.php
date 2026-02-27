<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\UserDiscountResource\Pages;

use App\Filament\Sales\Resources\UserDiscountResource;
use Filament\Resources\Pages\ListRecords;

class ListUserDiscounts extends ListRecords
{
    protected static string $resource = UserDiscountResource::class;
}
