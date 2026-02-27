<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\GuestMessageResource\Pages;

use App\Filament\Telop\Resources\GuestMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestMessage extends CreateRecord
{
    protected static string $resource = GuestMessageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
