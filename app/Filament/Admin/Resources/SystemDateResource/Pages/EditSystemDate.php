<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SystemDateResource\Pages;

use App\Filament\Admin\Resources\SystemDateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSystemDate extends EditRecord
{
    protected static string $resource = SystemDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
