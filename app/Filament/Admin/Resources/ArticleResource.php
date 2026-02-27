<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\ArticleType;
use App\Filament\Admin\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::DocumentText;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('article_no')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->label('Article No'),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('department_id')
                                    ->relationship('department', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('type')
                                    ->options(ArticleType::class)
                                    ->default(ArticleType::Sales),
                                TextInput::make('default_price')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR'),
                                Toggle::make('tax_inclusive')
                                    ->default(true),
                                Toggle::make('is_active')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article_no')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Article No'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('default_price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('tax_inclusive')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department'),
                Tables\Filters\SelectFilter::make('type')
                    ->options(ArticleType::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
