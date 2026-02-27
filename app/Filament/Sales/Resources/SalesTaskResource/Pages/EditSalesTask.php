<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesTaskResource\Pages;

use App\Filament\Sales\Resources\SalesTaskResource;
use Filament\Resources\Pages\EditRecord;

class EditSalesTask extends EditRecord
{
    protected static string $resource = SalesTaskResource::class;
}
