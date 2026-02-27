<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\LostAndFoundResource\Pages;

use App\Filament\Hk\Resources\LostAndFoundResource;
use Filament\Resources\Pages\ListRecords;

class ListLostAndFounds extends ListRecords
{
    protected static string $resource = LostAndFoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
