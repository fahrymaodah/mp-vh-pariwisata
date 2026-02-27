<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\Pages;

use App\Filament\Fo\Resources\GuestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;
}
