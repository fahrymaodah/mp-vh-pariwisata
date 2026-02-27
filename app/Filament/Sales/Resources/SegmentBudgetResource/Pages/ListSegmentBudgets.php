<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SegmentBudgetResource\Pages;

use App\Filament\Sales\Resources\SegmentBudgetResource;
use Filament\Resources\Pages\ListRecords;

class ListSegmentBudgets extends ListRecords
{
    protected static string $resource = SegmentBudgetResource::class;
}
