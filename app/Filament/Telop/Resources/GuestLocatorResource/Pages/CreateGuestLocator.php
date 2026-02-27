<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\GuestLocatorResource\Pages;

use App\Filament\Telop\Resources\GuestLocatorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestLocator extends CreateRecord
{
    protected static string $resource = GuestLocatorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
