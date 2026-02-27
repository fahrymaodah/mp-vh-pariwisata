<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ScenarioResource\Pages;

use App\Filament\Admin\Resources\ScenarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScenarios extends ListRecords
{
    protected static string $resource = ScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
