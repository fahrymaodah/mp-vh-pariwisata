<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MembershipCardTypeResource\Pages;
use App\Models\MembershipCardType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class MembershipCardTypeResource extends Resource
{
    protected static ?string $model = MembershipCardType::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CreditCard;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Membership Card Types';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership Card Type')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('discount_percentage')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                        Textarea::make('description')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('memberships_count')
                    ->label('Members')
                    ->counts('memberships')
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
            'index' => Pages\ListMembershipCardTypes::route('/'),
            'create' => Pages\CreateMembershipCardType::route('/create'),
            'edit' => Pages\EditMembershipCardType::route('/{record}/edit'),
        ];
    }
}
