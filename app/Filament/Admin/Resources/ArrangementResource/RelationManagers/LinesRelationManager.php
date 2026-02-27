<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ArrangementResource\RelationManagers;

use App\Enums\PostingType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('article_id')
                            ->relationship('article', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR'),
                        Select::make('posting_type')
                            ->options(PostingType::class)
                            ->default(PostingType::Daily),
                        TextInput::make('total_posting')
                            ->numeric()
                            ->nullable(),
                        Select::make('guest_type')
                            ->options([
                                'adult' => 'Adult',
                                'child' => 'Child',
                            ])
                            ->default('adult'),
                        Toggle::make('included_in_room_rate')
                            ->default(true),
                        Toggle::make('qty_always_one')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('article.name')
            ->columns([
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\TextColumn::make('article.name'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('posting_type')
                    ->badge(),
                Tables\Columns\IconColumn::make('included_in_room_rate')
                    ->boolean(),
                Tables\Columns\TextColumn::make('guest_type')
                    ->badge(),
            ])
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
