<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ArrangementResource\Pages;

use App\Filament\Admin\Resources\ArrangementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArrangements extends ListRecords
{
    protected static string $resource = ArrangementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
