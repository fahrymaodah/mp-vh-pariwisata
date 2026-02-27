<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\LinenTypeResource\Pages;

use App\Filament\Hk\Resources\LinenTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLinenType extends EditRecord
{
    protected static string $resource = LinenTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
