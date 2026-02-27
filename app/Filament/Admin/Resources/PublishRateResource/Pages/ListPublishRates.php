<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PublishRateResource\Pages;

use App\Filament\Admin\Resources\PublishRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPublishRates extends ListRecords
{
    protected static string $resource = PublishRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
