<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\CallLogResource\Pages;

use App\Filament\Telop\Resources\CallLogResource;
use Filament\Resources\Pages\ListRecords;

class ListCallLogs extends ListRecords
{
    protected static string $resource = CallLogResource::class;
}
