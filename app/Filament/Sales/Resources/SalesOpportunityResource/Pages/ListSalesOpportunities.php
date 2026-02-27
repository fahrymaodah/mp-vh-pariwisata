<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesOpportunityResource\Pages;

use App\Filament\Sales\Resources\SalesOpportunityResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesOpportunities extends ListRecords
{
    protected static string $resource = SalesOpportunityResource::class;
}
