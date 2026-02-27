<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources\PhoneDirectoryResource\Pages;

use App\Filament\Telop\Resources\PhoneDirectoryResource;
use Filament\Resources\Pages\ListRecords;

class ListPhoneDirectories extends ListRecords
{
    protected static string $resource = PhoneDirectoryResource::class;
}
