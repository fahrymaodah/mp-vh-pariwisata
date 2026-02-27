<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesTaskResource\Pages;

use App\Filament\Sales\Resources\SalesTaskResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesTasks extends ListRecords
{
    protected static string $resource = SalesTaskResource::class;
}
