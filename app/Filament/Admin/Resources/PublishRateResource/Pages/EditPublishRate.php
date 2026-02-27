<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PublishRateResource\Pages;

use App\Filament\Admin\Resources\PublishRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublishRate extends EditRecord
{
    protected static string $resource = PublishRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
