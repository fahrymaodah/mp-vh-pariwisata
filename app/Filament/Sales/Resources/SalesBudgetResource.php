<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SalesBudgetResource\Pages;
use App\Models\SalesBudget;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use App\Enums\UserRole;
use UnitEnum;

class SalesBudgetResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::salesManagerRoles()) ?? false;
    }

    protected static ?string $model = SalesBudget::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::Banknotes;
    protected static string | UnitEnum | null $navigationGroup = 'Budget';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = 'Sales Turnover Budget';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sales Turnover Budget')
                ->description('Monthly budget targets per sales person')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('user_id')
                            ->label('Sales Person')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('month')
                            ->label('Month')
                            ->displayFormat('F Y')
                            ->required(),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('lodging')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('fb')
                            ->label('F&B')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('others')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('room_nights')
                            ->label('Room Nights')
                            ->numeric()
                            ->integer()
                            ->default(0),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Sales Person')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->date('F Y')
                    ->sortable(),
                TextColumn::make('lodging')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('fb')
                    ->label('F&B')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('others')
                    ->money('IDR')
                    ->toggleable(),
                TextColumn::make('room_nights')
                    ->label('Room Nights')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Sales Person')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('month', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesBudgets::route('/'),
            'create' => Pages\CreateSalesBudget::route('/create'),
            'edit' => Pages\EditSalesBudget::route('/{record}/edit'),
        ];
    }
}
