<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesTaskResource\Pages;

use App\Filament\Sales\Resources\SalesTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesTask extends CreateRecord
{
    protected static string $resource = SalesTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
