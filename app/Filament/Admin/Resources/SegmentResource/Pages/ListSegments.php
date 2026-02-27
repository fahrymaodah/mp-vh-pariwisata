<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SegmentResource\Pages;

use App\Filament\Admin\Resources\SegmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSegments extends ListRecords
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
