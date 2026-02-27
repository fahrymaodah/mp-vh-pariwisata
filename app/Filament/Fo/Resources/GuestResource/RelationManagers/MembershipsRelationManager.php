<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\RelationManagers;

use App\Models\MembershipCardType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberships';

    protected static ?string $title = 'Membership Cards';

    protected static ?string $recordTitleAttribute = 'card_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('membership_card_type_id')
                            ->label('Card Type')
                            ->options(MembershipCardType::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('card_number')
                            ->label('Card Number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                    ]),
                Grid::make(3)
                    ->schema([
                        DatePicker::make('valid_from')
                            ->required(),
                        DatePicker::make('valid_until')
                            ->required()
                            ->afterOrEqual('valid_from'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cardType.name')
                    ->label('Card Type')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('card_number')
                    ->label('Card Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->valid_until?->isPast() ? 'danger' : ($record->valid_until?->diffInDays(now()) <= 30 ? 'warning' : null)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cardType.discount_percentage')
                    ->label('Discount %')
                    ->suffix('%'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
