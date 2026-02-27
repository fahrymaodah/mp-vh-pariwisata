<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ArrangementResource\Pages;

use App\Filament\Admin\Resources\ArrangementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArrangement extends CreateRecord
{
    protected static string $resource = ArrangementResource::class;
}
