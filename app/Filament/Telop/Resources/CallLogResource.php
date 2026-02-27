<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources;

use App\Filament\Telop\Resources\CallLogResource\Pages;
use App\Models\CallLog;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class CallLogResource extends Resource
{
    protected static ?string $model = CallLog::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::Phone;
    protected static string | UnitEnum | null $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Call Administration';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Call Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('extension_no')
                            ->label('Extension No')
                            ->maxLength(20),
                        TextInput::make('dialed_number')
                            ->label('Dialed Number')
                            ->required()
                            ->maxLength(50),
                        Select::make('call_type')
                            ->options([
                                'outgoing' => 'Outgoing',
                                'incoming' => 'Incoming',
                                'internal' => 'Internal',
                            ])
                            ->default('outgoing')
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        DatePicker::make('call_date')
                            ->required()
                            ->default(now()),
                        TextInput::make('call_time')
                            ->placeholder('HH:MM:SS'),
                        TextInput::make('duration')
                            ->label('Duration (seconds)')
                            ->numeric()
                            ->integer()
                            ->default(0),
                    ]),
                ]),
            Section::make('Billing')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('reservation_id')
                            ->label('Reservation')
                            ->relationship('reservation', 'reservation_no')
                            ->searchable()
                            ->preload(),
                        Select::make('room_id')
                            ->label('Room')
                            ->relationship('room', 'room_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('rate_amount')
                            ->label('Rate Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                    Grid::make(2)->schema([
                        Toggle::make('is_posted')
                            ->label('Posted to Bill')
                            ->default(false),
                        Select::make('posted_to_bill')
                            ->label('Bill Type')
                            ->options([
                                'guest' => 'Guest Bill',
                                'non-stay' => 'Non-Stay Bill',
                                'master' => 'Master Bill',
                            ])
                            ->visible(fn ($get) => $get('is_posted')),
                    ]),
                ]),
            Section::make('Notes')
                ->collapsible()
                ->schema([
                    Textarea::make('reason')
                        ->label('Call Reason / Description')
                        ->rows(3)
                        ->maxLength(65535),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('extension_no')
                    ->label('Ext')
                    ->searchable(),
                TextColumn::make('call_date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('call_time')
                    ->label('Time'),
                TextColumn::make('dialed_number')
                    ->label('Dialed')
                    ->searchable(),
                TextColumn::make('call_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'outgoing' => 'info',
                        'incoming' => 'success',
                        'internal' => 'gray',
                        default => 'secondary',
                    }),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(fn (int $state): string => gmdate('H:i:s', $state))
                    ->sortable(),
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                TextColumn::make('rate_amount')
                    ->label('Rate')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('is_posted')
                    ->label('Posted')
                    ->boolean(),
                TextColumn::make('reason')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('call_type')
                    ->options([
                        'outgoing' => 'Outgoing',
                        'incoming' => 'Incoming',
                        'internal' => 'Internal',
                    ]),
                TernaryFilter::make('is_posted')
                    ->label('Posted'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('postToBill')
                    ->label('Post to Bill')
                    ->icon(Heroicon::CurrencyDollar)
                    ->color('success')
                    ->visible(fn (CallLog $record): bool => ! $record->is_posted)
                    ->requiresConfirmation()
                    ->form([
                        Select::make('posted_to_bill')
                            ->label('Bill Type')
                            ->options([
                                'guest' => 'Guest Bill',
                                'non-stay' => 'Non-Stay Bill',
                                'master' => 'Master Bill',
                            ])
                            ->required(),
                    ])
                    ->action(function (CallLog $record, array $data): void {
                        $record->update([
                            'is_posted' => true,
                            'posted_to_bill' => $data['posted_to_bill'],
                        ]);
                    }),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('call_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallLogs::route('/'),
            'create' => Pages\CreateCallLog::route('/create'),
            'edit' => Pages\EditCallLog::route('/{record}/edit'),
        ];
    }
}
