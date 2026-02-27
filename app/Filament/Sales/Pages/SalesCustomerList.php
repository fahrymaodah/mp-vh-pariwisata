<?php

declare(strict_types=1);

namespace App\Filament\Sales\Pages;

use App\Models\Guest;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\GuestType;
use BackedEnum;

class SalesCustomerList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.sales.pages.sales-customer-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Users;
    protected static ?string $navigationLabel = 'Customer List';
    protected static ?string $title = 'Sales Customer List';
    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Guest::query()
                    ->where('sales_user_id', auth()->id())
            )
            ->columns([
                TextColumn::make('guest_no')
                    ->label('Guest No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('company_title')
                    ->label('Title')
                    ->toggleable(),
                TextColumn::make('address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->toggleable(),
                TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Create Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(GuestType::class)
                    ->label('Card Type'),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
