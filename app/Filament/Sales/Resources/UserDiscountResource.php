<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\UserDiscountResource\Pages;
use App\Models\User;
use App\Models\UserDiscount;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class UserDiscountResource extends Resource
{
    protected static ?string $model = UserDiscount::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ReceiptPercent;
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationLabel = 'User Discount Rates';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('User Discount Setup')
                ->description('Define discount percentages allowed for each user')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('discount_percent')
                            ->label('Discount %')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        TextInput::make('description')
                            ->maxLength(255)
                            ->placeholder('e.g. Manager discount'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_percent')
                    ->label('Discount %')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('description')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('user_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserDiscounts::route('/'),
            'create' => Pages\CreateUserDiscount::route('/create'),
            'edit' => Pages\EditUserDiscount::route('/{record}/edit'),
        ];
    }
}
