<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Department;
use App\Models\Guest;
use App\Models\Invoice;
use App\Services\BillingService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class InvoiceNsgPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.invoice-nsg';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserPlus;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Invoice NSG';

    protected static ?string $title = 'Invoice Non-Stay Guest';

    protected static ?string $slug = 'invoice-nsg';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('new_nsg_invoice')
                ->label('New NSG Invoice')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->form([
                    Select::make('department_id')
                        ->label('Select Department')
                        ->options(Department::active()->pluck('name', 'id'))
                        ->required()
                        ->helperText('NSG invoices require department selection first.'),
                    Select::make('guest_id')
                        ->label('Outsider / Non-Stay Guest')
                        ->options(
                            Guest::query()
                                ->whereDoesntHave('reservations', fn ($q) => $q->where('status', 'checked_in'))
                                ->get()
                                ->mapWithKeys(fn (Guest $g) => [$g->id => $g->full_name])
                        )
                        ->searchable()
                        ->required()
                        ->helperText('Guest must be registered in Guest Card File.'),
                ])
                ->action(function (array $data) {
                    $invoice = Invoice::create([
                        'guest_id' => $data['guest_id'],
                        'department_id' => $data['department_id'],
                        'type' => InvoiceType::NonStayGuest,
                        'status' => InvoiceStatus::Open,
                        'created_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title("NSG Invoice {$invoice->invoice_no} created")
                        ->success()
                        ->send();

                    return redirect(InvoiceDetailPage::getUrl(['record' => $invoice->id]));
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('type', InvoiceType::NonStayGuest)
                    ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
                    ->with(['guest', 'department'])
            )
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Bill No')
                    ->searchable()
                    ->badge()
                    ->color('purple'),
                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->weight('bold'),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->badge()
                    ->color('info'),
                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('total_payment')
                    ->label('Payment')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state) => $state->color()),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Invoice $record) => InvoiceDetailPage::getUrl(['record' => $record->id]))
            ->emptyStateHeading('No NSG invoices')
            ->emptyStateDescription('Create a new Non-Stay Guest invoice with the button above.')
            ->emptyStateIcon(Heroicon::UserPlus);
    }
}
