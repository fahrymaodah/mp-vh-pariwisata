<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\GuestMessageResource\Pages;

use App\Filament\Telop\Resources\GuestMessageResource;
use Filament\Resources\Pages\EditRecord;

class EditGuestMessage extends EditRecord
{
    protected static string $resource = GuestMessageResource::class;
}
