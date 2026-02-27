<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources\SalesOpportunityResource\Pages;

use App\Filament\Sales\Resources\SalesOpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesOpportunity extends CreateRecord
{
    protected static string $resource = SalesOpportunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
