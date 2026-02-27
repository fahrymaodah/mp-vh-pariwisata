<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HotelResource\Pages;
use App\Models\Hotel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
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

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BuildingOffice2;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hotel Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(50),
                                TextInput::make('fax')
                                    ->maxLength(50),
                                TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('address')
                            ->rows(3),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('city')
                                    ->maxLength(100),
                                TextInput::make('country')
                                    ->maxLength(100),
                            ]),
                    ]),
                Section::make('Financial Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->default('IDR')
                                    ->maxLength(10),
                                TextInput::make('tax_percentage')
                                    ->numeric()
                                    ->default(11.00)
                                    ->suffix('%'),
                                TextInput::make('service_percentage')
                                    ->numeric()
                                    ->default(10.00)
                                    ->suffix('%'),
                            ]),
                        TimePicker::make('checkout_time')
                            ->default('12:00'),
                    ]),
                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->image()
                            ->directory('hotel-logos')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('currency_code')
                    ->badge(),
                Tables\Columns\TextColumn::make('tax_percentage')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('service_percentage')
                    ->suffix('%'),
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
            'index' => Pages\ListHotels::route('/'),
            'create' => Pages\CreateHotel::route('/create'),
            'edit' => Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}
