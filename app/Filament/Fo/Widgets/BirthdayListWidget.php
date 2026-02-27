<?php

declare(strict_types=1);

namespace App\Filament\Fo\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Guest;
use App\Models\Reservation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BirthdayListWidget extends BaseWidget
{
    protected static ?string $heading = '🎂 Birthday Today — In-House Guests';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show if there are in-house guests with birthday today
        return self::getBirthdayQuery()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(self::getBirthdayQuery())
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random')
                    ->width(40),
                Tables\Columns\TextColumn::make('guest_no')
                    ->label('Guest No')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn (Guest $record) => $record->fullName),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Birth Date')
                    ->date(),
                Tables\Columns\TextColumn::make('age')
                    ->label('Age')
                    ->state(fn (Guest $record): string => $record->birth_date ? $record->birth_date->age . ' years' : '-'),
                Tables\Columns\TextColumn::make('current_room')
                    ->label('Room')
                    ->state(function (Guest $record): string {
                        $reservation = $record->reservations()
                            ->where('status', ReservationStatus::CheckedIn)
                            ->with('room')
                            ->first();
                        return $reservation?->room?->room_number ?? '-';
                    }),
                Tables\Columns\IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean(),
            ])
            ->paginated(false);
    }

    protected static function getBirthdayQuery(): Builder
    {
        $today = now();

        return Guest::query()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->whereHas('reservations', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn));
    }
}
