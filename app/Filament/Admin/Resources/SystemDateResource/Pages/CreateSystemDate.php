<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SystemDateResource\Pages;

use App\Filament\Admin\Resources\SystemDateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemDate extends CreateRecord
{
    protected static string $resource = SystemDateResource::class;
}
