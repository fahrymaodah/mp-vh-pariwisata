<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Models\Hotel;
use App\Models\Reservation;
use Filament\Pages\Page;
use Filament\Panel;

class PrintConfirmationLetter extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.fo.pages.print-confirmation-letter';

    public static function getRoutePath(Panel|string|null $panel = null): string
    {
        return '/print-confirmation-letter/{record}';
    }

    public ?Reservation $reservation = null;

    public ?Hotel $hotel = null;

    public function mount(int $record): void
    {
        $this->reservation = Reservation::with(['guest', 'roomCategory', 'room', 'arrangement'])->findOrFail($record);
        $this->hotel = Hotel::first();
    }

    public function getTitle(): string
    {
        return "Confirmation Letter — {$this->reservation->reservation_no}";
    }
}
