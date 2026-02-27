<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\Pages;

use App\Filament\Fo\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Reservation $reservation */
        $reservation = $this->record;

        $changes = $reservation->getChanges();
        unset($changes['updated_at']);

        foreach ($changes as $field => $newValue) {
            $oldValue = $reservation->getOriginal($field);

            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'modified',
                'field_changed' => $field,
                'old_value' => is_object($oldValue) && method_exists($oldValue, 'value') ? $oldValue->value : (string) $oldValue,
                'new_value' => is_object($newValue) && method_exists($newValue, 'value') ? $newValue->value : (string) $newValue,
            ]);
        }
    }
}
