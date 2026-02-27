<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesActivityResource\Pages;

use App\Filament\Sales\Resources\SalesActivityResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesActivities extends ListRecords
{
    protected static string $resource = SalesActivityResource::class;
}
