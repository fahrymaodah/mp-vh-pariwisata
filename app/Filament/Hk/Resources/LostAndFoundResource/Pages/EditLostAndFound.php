<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\LostAndFoundResource\Pages;

use App\Filament\Hk\Resources\LostAndFoundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLostAndFound extends EditRecord
{
    protected static string $resource = LostAndFoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
