<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\DepartmentType;
use App\Filament\Admin\Resources\DepartmentResource\Pages;
use App\Models\Department;
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
use Filament\Actions;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BuildingOffice;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100),
                                Select::make('type')
                                    ->options(DepartmentType::class)
                                    ->default(DepartmentType::Hotel),
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
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label('Articles'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(DepartmentType::class),
            ])
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
