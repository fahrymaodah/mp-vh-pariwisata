<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TutorialResource\Pages;

use App\Filament\Admin\Resources\TutorialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTutorial extends EditRecord
{
    protected static string $resource = TutorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
