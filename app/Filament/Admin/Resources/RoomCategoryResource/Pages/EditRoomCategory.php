<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\RoomCategoryResource\Pages;

use App\Filament\Admin\Resources\RoomCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomCategory extends EditRecord
{
    protected static string $resource = RoomCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
