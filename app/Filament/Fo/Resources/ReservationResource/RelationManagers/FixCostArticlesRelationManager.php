<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\RelationManagers;

use App\Enums\PostingType;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FixCostArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'fixCostArticles';

    protected static ?string $title = 'Fix Cost Articles';

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
                        TextInput::make('qty')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->required(),
                        Select::make('posting_type')
                            ->options(PostingType::class)
                            ->default(PostingType::Daily)
                            ->required(),
                        TextInput::make('total_posting')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave blank for unlimited'),
                        DatePicker::make('start_from')
                            ->label('Start From')
                            ->nullable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('article.name')
            ->columns([
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('article.name')
                    ->label('Article')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('posting_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_posting')
                    ->alignCenter()
                    ->default('-'),
                Tables\Columns\TextColumn::make('start_from')
                    ->date('d M Y')
                    ->default('-'),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
