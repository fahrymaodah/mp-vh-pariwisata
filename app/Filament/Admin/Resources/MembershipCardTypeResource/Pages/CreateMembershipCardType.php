<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\MembershipCardTypeResource\Pages;

use App\Filament\Admin\Resources\MembershipCardTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMembershipCardType extends CreateRecord
{
    protected static string $resource = MembershipCardTypeResource::class;
}
