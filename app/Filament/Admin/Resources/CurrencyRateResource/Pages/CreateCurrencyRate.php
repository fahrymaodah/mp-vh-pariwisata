<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CurrencyRateResource\Pages;

use App\Filament\Admin\Resources\CurrencyRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrencyRate extends CreateRecord
{
    protected static string $resource = CurrencyRateResource::class;
}
