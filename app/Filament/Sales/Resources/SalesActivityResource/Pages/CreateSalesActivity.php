<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesActivityResource\Pages;

use App\Filament\Sales\Resources\SalesActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesActivity extends CreateRecord
{
    protected static string $resource = SalesActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
