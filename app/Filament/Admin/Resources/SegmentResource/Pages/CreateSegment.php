<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SegmentResource\Pages;

use App\Filament\Admin\Resources\SegmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSegment extends CreateRecord
{
    protected static string $resource = SegmentResource::class;
}
