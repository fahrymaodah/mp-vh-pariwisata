<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesBudgetResource\Pages;

use App\Filament\Sales\Resources\SalesBudgetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesBudget extends CreateRecord
{
    protected static string $resource = SalesBudgetResource::class;
}
