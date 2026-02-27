<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\OooRoomResource\Pages;

use App\Filament\Hk\Resources\OooRoomResource;
use Filament\Resources\Pages\ListRecords;

class ListOooRooms extends ListRecords
{
    protected static string $resource = OooRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
