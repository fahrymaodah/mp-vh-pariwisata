<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CurrencyRateResource\Pages;

use App\Filament\Admin\Resources\CurrencyRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCurrencyRate extends EditRecord
{
    protected static string $resource = CurrencyRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
