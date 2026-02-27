<?php

declare(strict_types=1);

namespace App\Filament\Sales\Pages;

use App\Models\Guest;
use App\Models\GuestContact;
use App\Models\SalesActivity;
use App\Models\SalesOpportunity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class ContactDetail extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.sales.pages.contact-detail';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserCircle;
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Contact Detail';
    protected static ?string $title = 'Contact Detail';
    protected static ?int $navigationSort = 6;

    public ?int $selectedGuestId = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GuestContact::query()
                    ->when($this->selectedGuestId, fn ($q) => $q->where('guest_id', $this->selectedGuestId))
                    ->when(!$this->selectedGuestId, fn ($q) => $q->whereHas('guest', fn ($gq) => $gq->where('sales_user_id', auth()->id())))
            )
            ->columns([
                TextColumn::make('guest.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Contact Name')
                    ->searchable(['name', 'first_name']),
                TextColumn::make('department')
                    ->toggleable(),
                TextColumn::make('function')
                    ->label('Position')
                    ->toggleable(),
                TextColumn::make('extension')
                    ->label('Ext.')
                    ->toggleable(),
                TextColumn::make('email')
                    ->toggleable(),
                IconColumn::make('is_main')
                    ->label('Main')
                    ->boolean(),
            ])
            ->defaultSort('guest_id');
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('selectedGuestId')
                ->label('Company / Guest')
                ->options(function () {
                    return Guest::where('sales_user_id', auth()->id())
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->placeholder('All Customers')
                ->live(),
        ]);
    }

    public function getContactNotes(): array
    {
        if (!$this->selectedGuestId) {
            return [];
        }

        $guest = Guest::with(['contacts'])->find($this->selectedGuestId);
        if (!$guest) {
            return [];
        }

        return [
            'guest' => $guest,
            'contacts' => $guest->contacts,
            'activities' => SalesActivity::where('guest_id', $this->selectedGuestId)
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(10)
                ->get(),
            'opportunities' => SalesOpportunity::where('guest_id', $this->selectedGuestId)
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }
}
