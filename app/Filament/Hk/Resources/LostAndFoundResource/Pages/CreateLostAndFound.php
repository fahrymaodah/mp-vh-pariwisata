<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources\LostAndFoundResource\Pages;

use App\Filament\Hk\Resources\LostAndFoundResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLostAndFound extends CreateRecord
{
    protected static string $resource = LostAndFoundResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
