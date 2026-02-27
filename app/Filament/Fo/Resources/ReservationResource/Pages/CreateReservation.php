<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\Pages;

use App\Filament\Fo\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Reservation $reservation */
        $reservation = $this->record;

        $reservation->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => "Reservation {$reservation->reservation_no} created",
        ]);
    }
}
