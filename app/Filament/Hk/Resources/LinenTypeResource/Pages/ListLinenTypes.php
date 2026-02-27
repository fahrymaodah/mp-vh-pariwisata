<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\LinenTypeResource\Pages;

use App\Filament\Hk\Resources\LinenTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListLinenTypes extends ListRecords
{
    protected static string $resource = LinenTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
