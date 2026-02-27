<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\RoomCategoryResource\Pages;

use App\Filament\Admin\Resources\RoomCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoomCategory extends CreateRecord
{
    protected static string $resource = RoomCategoryResource::class;
}
