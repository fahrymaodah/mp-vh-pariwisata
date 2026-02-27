<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\Pages;

use App\Enums\GuestType;
use App\Enums\ReservationStatus;
use App\Filament\Fo\Resources\ReservationResource;
use App\Models\Guest;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reservation Overview')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('reservation_no')
                                    ->label('Res. No')
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('guest.full_name')
                                    ->label('Guest'),
                                TextEntry::make('guest.type')
                                    ->label('Guest Type')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('arrival_date')
                                    ->date('d M Y'),
                                TextEntry::make('departure_date')
                                    ->date('d M Y'),
                                TextEntry::make('nights'),
                                TextEntry::make('adults'),
                                TextEntry::make('children'),
                            ]),
                    ]),

                Section::make('Room & Rate')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('roomCategory.name')
                                    ->label('Category'),
                                TextEntry::make('room.room_number')
                                    ->label('Room')
                                    ->badge()
                                    ->color('warning')
                                    ->default('Not Assigned'),
                                TextEntry::make('arrangement.description')
                                    ->label('Arrangement')
                                    ->default('-'),
                                TextEntry::make('room_rate')
                                    ->money('IDR'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('room_qty')
                                    ->label('Room Qty'),
                                TextEntry::make('currency_code'),
                                IconEntry::make('is_fix_rate')
                                    ->label('Fix Rate')
                                    ->boolean(),
                                TextEntry::make('bill_instruction')
                                    ->label('Bill Instruction')
                                    ->default('-'),
                            ]),
                    ]),

                Section::make('Main Reservation Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('segment.description')
                                    ->label('Segment')
                                    ->default('-'),
                                TextEntry::make('reserved_by')
                                    ->default('-'),
                                TextEntry::make('source')
                                    ->default('-'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('group_name')
                                    ->label('Group Name')
                                    ->default('-'),
                                TextEntry::make('ta_commission')
                                    ->label('T/A Commission')
                                    ->money('IDR'),
                                TextEntry::make('letter_no')
                                    ->label('Letter No')
                                    ->default('-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('purpose')
                                    ->default('-'),
                                TextEntry::make('comments')
                                    ->default('-'),
                            ]),
                    ]),

                Section::make('Flight & Transport')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('flight_no')
                                    ->label('Flight')
                                    ->default('-'),
                                TextEntry::make('eta')
                                    ->label('ETA')
                                    ->default('-'),
                                TextEntry::make('etd')
                                    ->label('ETD')
                                    ->default('-'),
                                IconEntry::make('is_pickup')
                                    ->label('Pickup')
                                    ->boolean(),
                                IconEntry::make('is_dropoff')
                                    ->label('Drop-off')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Deposit')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('deposit_limit_date')
                                    ->label('Limit Date')
                                    ->date('d M Y')
                                    ->default('-'),
                                TextEntry::make('deposit_amount')
                                    ->label('Amount')
                                    ->money('IDR'),
                                TextEntry::make('deposit_paid')
                                    ->label('Paid')
                                    ->money('IDR'),
                                TextEntry::make('deposit_balance')
                                    ->label('Balance')
                                    ->money('IDR')
                                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                            ]),
                    ]),

                Section::make('Master Bill')
                    ->visible(fn (Reservation $record) => (bool) $record->is_master_bill)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_master_bill')
                                    ->label('Active')
                                    ->boolean(),
                                TextEntry::make('master_bill_receiver')
                                    ->label('Bill Receiver'),
                            ]),
                    ]),

                Section::make('Special Flags')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('is_incognito')
                                    ->label('Incognito')
                                    ->boolean(),
                                IconEntry::make('is_day_use')
                                    ->label('Day Use')
                                    ->boolean(),
                                IconEntry::make('is_room_sharer')
                                    ->label('Room Sharer')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Room Sharers')
                    ->visible(fn (Reservation $record) => $record->childReservations()->count() > 0)
                    ->schema([
                        RepeatableEntry::make('childReservations')
                            ->label('')
                            ->schema([
                                TextEntry::make('guest.full_name')->label('Guest'),
                                TextEntry::make('arrival_date')->date('d M Y'),
                                TextEntry::make('departure_date')->date('d M Y'),
                                TextEntry::make('status')->badge(),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Audit Trail')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('createdBy.name')
                                    ->label('Created By'),
                                TextEntry::make('created_at')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        /** @var Reservation $record */
        $record = $this->record;

        return [
            Actions\EditAction::make(),

            // Print Confirmation Letter
            Actions\Action::make('printConfirmation')
                ->label('Confirmation Letter')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn () => route('filament.fo.pages.print-confirmation-letter', ['record' => $record->id]))
                ->openUrlInNewTab(),

            // Print Registration Form
            Actions\Action::make('printRegistration')
                ->label('Registration Form')
                ->icon(Heroicon::DocumentText)
                ->color('gray')
                ->url(fn () => route('filament.fo.pages.print-registration-form', ['record' => $record->id]))
                ->openUrlInNewTab(),

            // Change Status action
            Actions\Action::make('changeStatus')
                ->label('Change Status')
                ->icon(Heroicon::ArrowPath)
                ->color('warning')
                ->form([
                    Select::make('status')
                        ->options(function () use ($record) {
                            $options = [];
                            foreach (ReservationStatus::cases() as $status) {
                                if ($status->isActive() && $status !== $record->status) {
                                    $options[$status->value] = $status->label();
                                }
                            }
                            return $options;
                        })
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $oldStatus = $record->status;
                    $record->update(['status' => $data['status']]);

                    $record->logs()->create([
                        'user_id' => auth()->id(),
                        'action' => 'status_changed',
                        'field_changed' => 'status',
                        'old_value' => $oldStatus->value,
                        'new_value' => $data['status'],
                    ]);

                    Notification::make()
                        ->title('Status Updated')
                        ->body("Reservation status changed to {$data['status']}")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $record->status->isActive() && $record->status !== ReservationStatus::CheckedIn),

            // Room Sharer action
            Actions\Action::make('addRoomSharer')
                ->label('Add Room Sharer')
                ->icon(Heroicon::UserPlus)
                ->color('info')
                ->form([
                    Select::make('guest_id')
                        ->label('Room Sharer Guest')
                        ->options(function () use ($record) {
                            return Guest::where('id', '!=', $record->guest_id)
                                ->where('type', GuestType::Individual)
                                ->get()
                                ->pluck('full_name', 'id');
                        })
                        ->searchable()
                        ->required(),
                    DatePicker::make('arrival_date')
                        ->default(fn () => $record->arrival_date?->format('Y-m-d'))
                        ->required(),
                    DatePicker::make('departure_date')
                        ->default(fn () => $record->departure_date?->format('Y-m-d'))
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $sharer = Reservation::create([
                        'guest_id' => $data['guest_id'],
                        'status' => $record->status,
                        'arrival_date' => $data['arrival_date'],
                        'departure_date' => $data['departure_date'],
                        'nights' => \Carbon\Carbon::parse($data['arrival_date'])->diffInDays(\Carbon\Carbon::parse($data['departure_date'])),
                        'adults' => 1,
                        'children' => 0,
                        'room_category_id' => $record->room_category_id,
                        'room_id' => $record->room_id,
                        'room_qty' => 0,
                        'arrangement_id' => $record->arrangement_id,
                        'room_rate' => 0,
                        'is_room_sharer' => true,
                        'parent_reservation_id' => $record->id,
                        'created_by' => auth()->id(),
                    ]);

                    $record->logs()->create([
                        'user_id' => auth()->id(),
                        'action' => 'room_sharer_added',
                        'field_changed' => null,
                        'old_value' => null,
                        'new_value' => "Room sharer added: {$sharer->guest->full_name}",
                    ]);

                    Notification::make()
                        ->title('Room Sharer Added')
                        ->body("Guest has been added as room sharer")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $record->status->isActive()),

            // Group Reservation Admin (3.5)
            Actions\Action::make('splitGroup')
                ->label('Split Group')
                ->icon(Heroicon::UserGroup)
                ->color('purple')
                ->form([
                    TextInput::make('member_count')
                        ->label('Number of Individual Reservations')
                        ->numeric()
                        ->minValue(2)
                        ->maxValue(50)
                        ->default(2)
                        ->required()
                        ->helperText('Creates individual child reservations from this group/company reservation'),
                ])
                ->action(function (array $data) use ($record) {
                    $count = (int) $data['member_count'];

                    for ($i = 1; $i <= $count; $i++) {
                        Reservation::create([
                            'guest_id' => $record->guest_id,
                            'status' => $record->status,
                            'arrival_date' => $record->arrival_date,
                            'departure_date' => $record->departure_date,
                            'nights' => $record->nights,
                            'adults' => 1,
                            'children' => 0,
                            'room_category_id' => $record->room_category_id,
                            'room_id' => null,
                            'room_qty' => 1,
                            'arrangement_id' => $record->arrangement_id,
                            'room_rate' => $record->room_rate,
                            'currency_code' => $record->currency_code,
                            'is_fix_rate' => $record->is_fix_rate,
                            'segment_id' => $record->segment_id,
                            'group_name' => $record->group_name ?: $record->guest?->full_name,
                            'parent_reservation_id' => $record->id,
                            'created_by' => auth()->id(),
                        ]);
                    }

                    $record->logs()->create([
                        'user_id' => auth()->id(),
                        'action' => 'group_split',
                        'field_changed' => null,
                        'old_value' => null,
                        'new_value' => "Group split into {$count} individual reservations",
                    ]);

                    Notification::make()
                        ->title('Group Split Complete')
                        ->body("{$count} individual reservations created")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $record->status->isActive() && $record->room_qty > 1 && ! $record->is_room_sharer),

            // Cancel Reservation
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('cancel_reason')
                        ->label('Cancellation Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record) {
                    $oldStatus = $record->status;
                    $record->update([
                        'status' => ReservationStatus::Cancelled,
                        'cancelled_at' => now(),
                        'cancel_reason' => $data['cancel_reason'],
                        'cancelled_by' => auth()->id(),
                    ]);

                    $record->logs()->create([
                        'user_id' => auth()->id(),
                        'action' => 'cancelled',
                        'field_changed' => 'status',
                        'old_value' => $oldStatus->value,
                        'new_value' => ReservationStatus::Cancelled->value,
                    ]);

                    Notification::make()
                        ->title('Reservation Cancelled')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $record->status->isActive() && $record->status !== ReservationStatus::CheckedIn),
        ];
    }
}
