<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\OooRoomResource\Pages;

use App\Filament\Hk\Resources\OooRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOooRoom extends EditRecord
{
    protected static string $resource = OooRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
