<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TutorialResource\Pages;

use App\Filament\Admin\Resources\TutorialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTutorial extends CreateRecord
{
    protected static string $resource = TutorialResource::class;
}
