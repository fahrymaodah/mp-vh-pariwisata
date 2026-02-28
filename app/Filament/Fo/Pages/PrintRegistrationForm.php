<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\Reservation;
use Filament\Pages\Page;
use Filament\Panel;

class PrintRegistrationForm extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::receptionRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.print-registration-form';

    public static function getRoutePath(Panel|string|null $panel = null): string
    {
        return '/print-registration-form/{record}';
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
        return "Registration Form — {$this->reservation->reservation_no}";
    }
}
