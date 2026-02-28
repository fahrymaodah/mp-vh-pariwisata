<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Models\LinenTransaction;
use App\Enums\UserRole;
use App\Models\LinenType;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LinenTransactions extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::hkSupervisorRoles()) ?? false;
    }

    protected string $view = 'filament.hk.pages.linen-transactions';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string | UnitEnum | null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Linen Transactions';

    protected static ?string $title = 'Linen Transactions';

    protected static ?string $slug = 'linen-transactions';

    public ?string $activeTab = 'incoming';

    public ?int $selectedLinenType = null;

    public ?int $transactionQty = null;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('activeTab')
                ->label('View')
                ->options([
                    'incoming' => 'Incoming',
                    'outgoing' => 'Outgoing',
                    'daily' => 'Daily Usage',
                ])
                ->default('incoming')
                ->live(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LinenTransaction::query()
                    ->with(['linenType', 'user'])
                    ->when($this->activeTab === 'incoming', fn ($q) => $q->where('type', 'incoming'))
                    ->when($this->activeTab === 'outgoing', fn ($q) => $q->where('type', 'outgoing'))
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('linenType.name')
                    ->label('Linen Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'incoming' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable()
                    ->alignCenter()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('user.name')
                    ->label('User'),
            ])
            ->filters([
                SelectFilter::make('linen_type_id')
                    ->label('Linen Type')
                    ->relationship('linenType', 'name'),
            ])
            ->bulkActions([])
            ->defaultSort('date', 'desc');
    }

    public function recordTransaction(string $type): void
    {
        if (!$this->selectedLinenType || !$this->transactionQty || $this->transactionQty <= 0) {
            Notification::make()
                ->title('Please select a linen type and enter a valid quantity')
                ->warning()
                ->send();
            return;
        }

        LinenTransaction::create([
            'linen_type_id' => $this->selectedLinenType,
            'type' => $type,
            'qty' => $this->transactionQty,
            'date' => SystemDate::today(),
            'user_id' => Auth::id(),
        ]);

        $linenName = LinenType::find($this->selectedLinenType)?->name;

        Notification::make()
            ->title("{$type} recorded: {$this->transactionQty} × {$linenName}")
            ->success()
            ->send();

        $this->selectedLinenType = null;
        $this->transactionQty = null;
    }

    public function getLinenTypes(): array
    {
        return LinenType::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
