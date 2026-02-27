<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\OooRoomResource\Pages;

use App\Filament\Hk\Resources\OooRoomResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOooRoom extends CreateRecord
{
    protected static string $resource = OooRoomResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
