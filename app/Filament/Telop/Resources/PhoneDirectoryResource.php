<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources;

use App\Filament\Telop\Resources\PhoneDirectoryResource\Pages;
use App\Models\PhoneDirectory;
use Filament\Actions;
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
use UnitEnum;

class PhoneDirectoryResource extends Resource
{
    protected static ?string $model = PhoneDirectory::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::BookOpen;
    protected static string | UnitEnum | null $navigationGroup = 'Directory';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Phone Directory';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact Information')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('department')
                            ->maxLength(100),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('contact_person')
                            ->maxLength(255),
                    ]),
                ]),
            Section::make('Phone & Extension')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('extension')
                            ->maxLength(20),
                        TextInput::make('mobile')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('fax')
                            ->maxLength(50),
                    ]),
                ]),
            Section::make('Address')
                ->collapsible()
                ->schema([
                    TextInput::make('address')
                        ->maxLength(255),
                    Grid::make(4)->schema([
                        TextInput::make('city')
                            ->maxLength(100),
                        TextInput::make('zip')
                            ->label('Zip Code')
                            ->maxLength(20),
                        TextInput::make('country')
                            ->maxLength(10),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('extension')
                    ->label('Ext')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->toggleable(),
                TextColumn::make('email')
                    ->toggleable(),
                TextColumn::make('contact_person')
                    ->label('Contact')
                    ->toggleable(),
                TextColumn::make('city')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->options(fn () => PhoneDirectory::query()
                        ->whereNotNull('department')
                        ->distinct()
                        ->pluck('department', 'department')
                        ->toArray()
                    ),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhoneDirectories::route('/'),
            'create' => Pages\CreatePhoneDirectory::route('/create'),
            'edit' => Pages\EditPhoneDirectory::route('/{record}/edit'),
        ];
    }
}
