<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\Pages;

use App\Filament\Fo\Resources\GuestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
