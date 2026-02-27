<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\CallLogResource\Pages;

use App\Filament\Telop\Resources\CallLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallLog extends CreateRecord
{
    protected static string $resource = CallLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
