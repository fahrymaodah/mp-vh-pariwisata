<?php

declare(strict_types=1);

namespace App\Filament\Telop\Pages;

use App\Models\Reservation;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class TelopGuestList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.telop.pages.telop-guest-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::PhoneArrowUpRight;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Guest List';
    protected static ?string $title = 'Telephone Operator — Guest List';

    public string $displayFilter = 'resident';

    public function setDisplayFilter(string $filter): void
    {
        $this->displayFilter = $filter;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                IconColumn::make('has_messages')
                    ->label('M')
                    ->tooltip('Has unread messages')
                    ->state(fn (Reservation $record): bool => $record->messages()->where('is_read', false)->exists())
                    ->boolean()
                    ->trueIcon(Heroicon::Envelope)
                    ->falseIcon(Heroicon::MinusSmall)
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('has_locator')
                    ->label('L')
                    ->tooltip('Locator active')
                    ->state(fn (Reservation $record): bool => $record->locators()->where('is_active', true)->exists())
                    ->boolean()
                    ->trueIcon(Heroicon::MapPin)
                    ->falseIcon(Heroicon::MinusSmall)
                    ->trueColor('info')
                    ->falseColor('gray'),
                TextColumn::make('reservation_no')
                    ->label('Res No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                TextColumn::make('guest.name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest.first_name')
                    ->label('First Name')
                    ->toggleable(),
                TextColumn::make('segment.description')
                    ->label('Segment')
                    ->toggleable(),
                TextColumn::make('guest.nationality')
                    ->label('Nation')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state instanceof \App\Enums\ReservationStatus => match ($state) {
                            \App\Enums\ReservationStatus::CheckedIn => 'success',
                            \App\Enums\ReservationStatus::Confirmed, \App\Enums\ReservationStatus::Tentative, \App\Enums\ReservationStatus::Guaranteed => 'info',
                            \App\Enums\ReservationStatus::CheckedOut => 'gray',
                            \App\Enums\ReservationStatus::Cancelled, \App\Enums\ReservationStatus::NoShow => 'danger',
                            default => 'secondary',
                        },
                        default => 'secondary',
                    }),
                IconColumn::make('is_incognito')
                    ->label('Incog.')
                    ->boolean()
                    ->trueIcon(Heroicon::EyeSlash)
                    ->falseIcon(Heroicon::MinusSmall)
                    ->trueColor('danger')
                    ->falseColor('gray'),
                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('departure_date')
                    ->label('Depart')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('toggleIncognito')
                    ->label(fn (Reservation $record): string => $record->is_incognito ? 'Remove Incognito' : 'Set Incognito')
                    ->icon(Heroicon::EyeSlash)
                    ->color(fn (Reservation $record): string => $record->is_incognito ? 'gray' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record): void {
                        $record->update(['is_incognito' => ! $record->is_incognito]);
                    }),
                Actions\Action::make('addMessage')
                    ->label('Message')
                    ->icon(Heroicon::EnvelopeOpen)
                    ->color('warning')
                    ->url(fn (Reservation $record): string => route('filament.telop.resources.guest-messages.create', ['reservation_id' => $record->id])),
                Actions\Action::make('setLocator')
                    ->label('Locator')
                    ->icon(Heroicon::MapPin)
                    ->color('info')
                    ->url(fn (Reservation $record): string => route('filament.telop.resources.guest-locators.create', ['reservation_id' => $record->id])),
            ])
            ->defaultSort('room_id');
    }

    protected function getFilteredQuery(): Builder
    {
        $query = Reservation::query()->with(['guest', 'room', 'segment']);
        $today = now()->toDateString();

        return match ($this->displayFilter) {
            'reservation' => $query->whereIn('status', [\App\Enums\ReservationStatus::Confirmed, \App\Enums\ReservationStatus::Tentative, \App\Enums\ReservationStatus::Guaranteed]),
            'resident' => $query->where('status', \App\Enums\ReservationStatus::CheckedIn),
            'arrival' => $query->where('arrival_date', $today),
            'depart' => $query->where('departure_date', $today),
            'departed' => $query->where('status', \App\Enums\ReservationStatus::CheckedOut),
            'all' => $query,
            default => $query->where('status', \App\Enums\ReservationStatus::CheckedIn),
        };
    }
}
