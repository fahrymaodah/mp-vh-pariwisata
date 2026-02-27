<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Main Contacts';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->maxLength(50),
                    ]),
                Grid::make(3)
                    ->schema([
                        TextInput::make('department')
                            ->maxLength(100),
                        TextInput::make('function')
                            ->label('Position / Function')
                            ->maxLength(100),
                        TextInput::make('extension')
                            ->label('Ext. Phone')
                            ->maxLength(20),
                    ]),
                Grid::make(3)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Birth Date'),
                        TextInput::make('birth_place')
                            ->label('Birth Place')
                            ->maxLength(100),
                    ]),
                Toggle::make('is_main')
                    ->label('Main Contact')
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_main')
                    ->label('Main')
                    ->boolean()
                    ->width(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('First Name'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('department'),
                Tables\Columns\TextColumn::make('function')
                    ->label('Position'),
                Tables\Columns\TextColumn::make('extension')
                    ->label('Ext'),
                Tables\Columns\TextColumn::make('email'),
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
