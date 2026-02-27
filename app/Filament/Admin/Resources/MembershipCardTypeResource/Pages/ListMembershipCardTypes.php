<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\MembershipCardTypeResource\Pages;

use App\Filament\Admin\Resources\MembershipCardTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembershipCardTypes extends ListRecords
{
    protected static string $resource = MembershipCardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
