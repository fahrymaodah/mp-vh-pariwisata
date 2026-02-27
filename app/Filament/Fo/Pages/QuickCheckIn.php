<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\GuestType;
use App\Enums\RoomStatus;
use App\Models\Arrangement;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Segment;
use App\Models\SystemDate;
use App\Services\CheckInService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class QuickCheckIn extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.fo.pages.quick-check-in';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Bolt;

    protected static string | UnitEnum | null $navigationGroup = 'Check-In';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Quick Check-In';

    protected static ?string $title = 'Quick Check-In (Walk-In)';

    protected static ?string $slug = 'quick-check-in';

    // Form state
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'arrival_date' => SystemDate::today(),
            'nights' => 1,
            'departure_date' => \Carbon\Carbon::parse(SystemDate::today())->addDay()->format('Y-m-d'),
            'adults' => 1,
            'children' => 0,
            'room_qty' => 1,
            'currency_code' => 'IDR',
            'source' => 'walk_in',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Guest Information')
                    ->icon(Heroicon::User)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('guest_id')
                                ->label('Select Existing Guest')
                                ->options(function () {
                                    return Guest::orderBy('name')
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn (Guest $g) => [$g->id => "{$g->guest_no} — {$g->full_name}"])
                                        ->toArray();
                                })
                                ->searchable()
                                ->placeholder('Search or create new guest below')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $guest = Guest::find($state);
                                        if ($guest) {
                                            $set('guest_name', $guest->name);
                                            $set('guest_first_name', $guest->first_name);
                                            $set('guest_phone', $guest->phone);
                                            $set('segment_id', $guest->main_segment_id);
                                        }
                                    }
                                }),
                            TextInput::make('guest_name')
                                ->label('Last Name / Company')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('guest_first_name')
                                ->label('First Name')
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('guest_phone')
                                ->label('Phone')
                                ->tel()
                                ->maxLength(50),
                            Select::make('segment_id')
                                ->label('Segment')
                                ->options(Segment::where('is_active', true)->get()->mapWithKeys(fn ($s) => [$s->id => "{$s->code} — {$s->description}"])->toArray())
                                ->searchable()
                                ->placeholder('Select segment'),
                        ]),
                    ]),

                Section::make('Stay Details')
                    ->icon(Heroicon::CalendarDays)
                    ->schema([
                        Grid::make(4)->schema([
                            DatePicker::make('arrival_date')
                                ->label('Arrival')
                                ->required()
                                ->default(fn () => SystemDate::today())
                                ->live()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state && $get('nights')) {
                                        $departure = \Carbon\Carbon::parse($state)->addDays((int) $get('nights'));
                                        $set('departure_date', $departure->format('Y-m-d'));
                                    }
                                }),
                            TextInput::make('nights')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state && $get('arrival_date')) {
                                        $departure = \Carbon\Carbon::parse($get('arrival_date'))->addDays((int) $state);
                                        $set('departure_date', $departure->format('Y-m-d'));
                                    }
                                }),
                            DatePicker::make('departure_date')
                                ->label('Departure')
                                ->required(),
                            TextInput::make('room_qty')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->minValue(1),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('adults')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                            TextInput::make('children')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Toggle::make('is_day_use')
                                ->label('Day Use')
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Room & Rate')
                    ->icon(Heroicon::Key)
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('room_category_id')
                                ->label('Category')
                                ->options(RoomCategory::pluck('name', 'id')->toArray())
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $category = RoomCategory::find($state);
                                        if ($category) {
                                            $set('room_rate', $category->base_rate);
                                        }
                                        $set('room_id', null);
                                    }
                                }),
                            Select::make('room_id')
                                ->label('Room No')
                                ->options(function (callable $get): array {
                                    $categoryId = $get('room_category_id');
                                    if (! $categoryId) {
                                        return [];
                                    }

                                    return Room::where('room_category_id', $categoryId)
                                        ->where('is_active', true)
                                        ->whereIn('status', [
                                            RoomStatus::VacantClean,
                                            RoomStatus::VacantCleanUnchecked,
                                            RoomStatus::VacantDirty,
                                        ])
                                        ->get()
                                        ->mapWithKeys(fn (Room $room) => [
                                            $room->id => "{$room->room_number} — {$room->status->label()} (Floor {$room->floor})",
                                        ])
                                        ->toArray();
                                })
                                ->required()
                                ->searchable(),
                            Select::make('arrangement_id')
                                ->label('Arrangement')
                                ->options(
                                    Arrangement::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(fn ($a) => [$a->id => "{$a->code} — {$a->description}"])
                                        ->toArray()
                                )
                                ->searchable()
                                ->placeholder('Select arrangement'),
                            TextInput::make('room_rate')
                                ->label('Room Rate')
                                ->numeric()
                                ->prefix('IDR')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('currency_code')
                                ->default('IDR')
                                ->maxLength(10),
                            TextInput::make('deposit_amount')
                                ->label('Deposit Amount')
                                ->numeric()
                                ->prefix('IDR')
                                ->default(0)
                                ->helperText('Deposit will be recorded during check-in'),
                        ]),
                    ]),
            ]);
    }

    public function processQuickCheckIn(): void
    {
        $data = $this->form->getState();

        try {
            // Create or use existing guest
            $guestId = $data['guest_id'] ?? null;

            if (! $guestId) {
                if (empty($data['guest_name'])) {
                    throw new \RuntimeException('Guest name is required.');
                }

                $guest = Guest::create([
                    'type' => GuestType::Individual,
                    'name' => $data['guest_name'],
                    'first_name' => $data['guest_first_name'] ?? null,
                    'phone' => $data['guest_phone'] ?? null,
                    'main_segment_id' => $data['segment_id'] ?? null,
                ]);
                $guestId = $guest->id;
            }

            $reservationData = [
                'guest_id' => $guestId,
                'arrival_date' => $data['arrival_date'],
                'departure_date' => $data['departure_date'],
                'nights' => $data['nights'],
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
                'room_category_id' => $data['room_category_id'],
                'room_id' => $data['room_id'],
                'room_qty' => $data['room_qty'] ?? 1,
                'arrangement_id' => $data['arrangement_id'] ?? null,
                'room_rate' => $data['room_rate'],
                'currency_code' => $data['currency_code'] ?? 'IDR',
                'segment_id' => $data['segment_id'] ?? null,
                'source' => 'walk_in',
                'is_day_use' => $data['is_day_use'] ?? false,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
            ];

            $depositAmount = (float) ($data['deposit_amount'] ?? 0);

            $reservation = app(CheckInService::class)->quickCheckIn($reservationData, $depositAmount);

            Notification::make()
                ->title('Quick Check-In Successful')
                ->body("Guest checked in to Room {$reservation->room->room_number} — Res. {$reservation->reservation_no}")
                ->success()
                ->send();

            // Reset form
            $this->form->fill([
                'arrival_date' => SystemDate::today(),
                'nights' => 1,
                'departure_date' => \Carbon\Carbon::parse(SystemDate::today())->addDay()->format('Y-m-d'),
                'adults' => 1,
                'children' => 0,
                'room_qty' => 1,
                'currency_code' => 'IDR',
                'source' => 'walk_in',
            ]);

            // Redirect to the reservation view
            $this->redirect(route('filament.fo.resources.reservations.view', $reservation));
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Quick Check-In Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
