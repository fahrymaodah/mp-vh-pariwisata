<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\PostingType;
use App\Filament\Admin\Resources\ArrangementResource\Pages;
use App\Filament\Admin\Resources\ArrangementResource\RelationManagers;
use App\Models\Arrangement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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

class ArrangementResource extends Resource
{
    protected static ?string $model = Arrangement::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static string | UnitEnum | null $navigationGroup = 'Rate Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Arrangement Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('description')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('invoice_label')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('currency_code')
                                    ->default('IDR')
                                    ->maxLength(10),
                                Select::make('lodging_article_id')
                                    ->relationship('lodgingArticle', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->label('Lodging Article'),
                                Select::make('arrangement_article_id')
                                    ->relationship('arrangementArticle', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->label('Arrangement Article'),
                                TextInput::make('min_stay')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_active')
                                    ->default(true),
                            ]),
                        Textarea::make('comments')
                            ->rows(3),
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
                Tables\Columns\TextColumn::make('invoice_label')
                    ->limit(30),
                Tables\Columns\TextColumn::make('currency_code')
                    ->badge(),
                Tables\Columns\TextColumn::make('min_stay'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('lines_count')
                    ->counts('lines')
                    ->label('Lines'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\LinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArrangements::route('/'),
            'create' => Pages\CreateArrangement::route('/create'),
            'edit' => Pages\EditArrangement::route('/{record}/edit'),
        ];
    }
}
