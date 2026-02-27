<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CurrencyRateResource\Pages;
use App\Models\CurrencyRate;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class CurrencyRateResource extends Resource
{
    protected static ?string $model = CurrencyRate::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Banknotes;

    protected static string | UnitEnum | null $navigationGroup = 'Rate Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Currency Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('description')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('purchase_rate')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('sales_rate')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('unit')
                                    ->numeric()
                                    ->default(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_rate')
                    ->numeric(decimalPlaces: 6)
                    ->sortable(),
                Tables\Columns\TextColumn::make('sales_rate')
                    ->numeric(decimalPlaces: 6)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencyRates::route('/'),
            'create' => Pages\CreateCurrencyRate::route('/create'),
            'edit' => Pages\EditCurrencyRate::route('/{record}/edit'),
        ];
    }
}
