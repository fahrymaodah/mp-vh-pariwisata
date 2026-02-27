<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ScenarioResource\Pages;

use App\Filament\Admin\Resources\ScenarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScenario extends CreateRecord
{
    protected static string $resource = ScenarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
