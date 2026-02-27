<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\HotelResource\Pages;

use App\Filament\Admin\Resources\HotelResource;
use Filament\Resources\Pages\ListRecords;

class ListHotels extends ListRecords
{
    protected static string $resource = HotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
