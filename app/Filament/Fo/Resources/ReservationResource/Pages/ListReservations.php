<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\Pages;

use App\Filament\Fo\Resources\ReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
