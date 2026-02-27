<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomStatusLog;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RoomStatusAdmin extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.hk.pages.room-status-admin';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowPath;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Room Status Admin';

    protected static ?string $title = 'Room Status Administration';

    protected static ?string $slug = 'room-status-admin';

    public ?string $statusFilter = null;

    public ?string $sortBy = 'room_number';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('statusFilter')
                ->label('Display')
                ->options([
                    '' => 'All',
                    'vacant_clean' => 'Vacant Clean Checked',
                    'vacant_clean_unchecked' => 'Vacant Clean Unchecked',
                    'vacant_dirty' => 'Vacant Uncleaned',
                    'expected_departure' => 'Expected Departure',
                    'occupied_dirty' => 'Occupied Dirty',
                    'occupied_clean' => 'Occupied Cleaned',
                    'out_of_order' => 'Out of Order',
                    'do_not_disturb' => 'Do Not Disturb',
                    'available' => 'Available Today',
                ])
                ->live(),
            Select::make('sortBy')
                ->label('Sort By')
                ->options([
                    'room_number' => 'Room Number',
                    'status' => 'Room Status',
                    'floor' => 'Floor',
                ])
                ->default('room_number')
                ->live(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::query()
                    ->active()
                    ->with(['category'])
                    ->when($this->statusFilter === 'available', fn ($q) => $q->available())
                    ->when($this->statusFilter && $this->statusFilter !== 'available', fn ($q) => $q->where('status', $this->statusFilter))
                    ->orderBy($this->sortBy === 'room_number' ? 'room_number' : ($this->sortBy === 'floor' ? 'floor' : 'status'))
            )
            ->columns([
                TextColumn::make('room_number')
                    ->label('Rm No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('category.code')
                    ->label('Cat')
                    ->sortable(),
                TextColumn::make('floor')
                    ->label('Floor')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (RoomStatus $state): string => $state->label())
                    ->color(fn (RoomStatus $state): string => $state->color()),
            ])
            ->actions([
                TableAction::make('setCleanChecked')
                    ->label('Clean Checked')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Room $record) => $this->changeStatus($record, RoomStatus::VacantClean))
                    ->hidden(fn (Room $record) => $record->status === RoomStatus::VacantClean),
                TableAction::make('setCleanUnchecked')
                    ->label('Clean Unchecked')
                    ->icon(Heroicon::Check)
                    ->color('lime')
                    ->requiresConfirmation()
                    ->action(fn (Room $record) => $this->changeStatus($record, RoomStatus::VacantCleanUnchecked))
                    ->hidden(fn (Room $record) => $record->status === RoomStatus::VacantCleanUnchecked),
                TableAction::make('setDirty')
                    ->label('Dirty')
                    ->icon(Heroicon::ExclamationTriangle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Room $record) => $this->changeStatus($record, RoomStatus::VacantDirty))
                    ->hidden(fn (Room $record) => $record->status === RoomStatus::VacantDirty),
                TableAction::make('setDND')
                    ->label('DND')
                    ->icon(Heroicon::NoSymbol)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (Room $record) => $this->changeStatus($record, RoomStatus::DoNotDisturb))
                    ->hidden(fn (Room $record) => $record->status === RoomStatus::DoNotDisturb),
                TableAction::make('setOccupiedClean')
                    ->label('Occ. Clean')
                    ->icon(Heroicon::Sparkles)
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn (Room $record) => $this->changeStatus($record, RoomStatus::OccupiedClean))
                    ->hidden(fn (Room $record) => !$record->status->isOccupied() || $record->status === RoomStatus::OccupiedClean),
            ])
            ->bulkActions([]);
    }

    public function changeStatus(Room $room, RoomStatus $newStatus): void
    {
        $oldStatus = $room->status;

        RoomStatusLog::create([
            'room_id' => $room->id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'changed_by' => Auth::id(),
        ]);

        $room->update(['status' => $newStatus]);

        Notification::make()
            ->title("Room {$room->room_number}: {$oldStatus->label()} → {$newStatus->label()}")
            ->success()
            ->send();
    }
}
