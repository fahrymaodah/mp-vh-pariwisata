<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SalesTaskResource\Pages;
use App\Models\SalesOpportunity;
use App\Models\SalesTask;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class SalesTaskResource extends Resource
{
    protected static ?string $model = SalesTask::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ClipboardDocumentCheck;
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Task List';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Task Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('sales_opportunity_id')
                            ->label('Opportunity')
                            ->relationship('opportunity', 'prospect_name')
                            ->searchable()
                            ->preload(),
                    ]),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make(3)->schema([
                        DatePicker::make('due_date')
                            ->label('Due Date'),
                        Toggle::make('is_completed')
                            ->label('Completed'),
                        Select::make('result')
                            ->options([
                                'clear' => 'Clear (Done)',
                                'erase' => 'Erase (Cancelled)',
                            ])
                            ->label('Result'),
                    ]),
                    Textarea::make('result_notes')
                        ->label('Result Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Attachment')
                ->schema([
                    FileUpload::make('attachment_path')
                        ->label('File Attachment')
                        ->directory('sales-tasks')
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(5120),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opportunity.prospect_name')
                    ->label('Opportunity')
                    ->toggleable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (SalesTask $record): string => $record->due_date && $record->due_date->isPast() && !$record->is_completed ? 'danger' : 'gray'),
                IconColumn::make('is_completed')
                    ->label('Done')
                    ->boolean(),
                TextColumn::make('result')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'clear' => 'success',
                        'erase' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                IconColumn::make('attachment_path')
                    ->label('File')
                    ->icon(fn (?string $state): string => $state ? 'heroicon-o-paper-clip' : '')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Sales')
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_completed')
                    ->label('Status')
                    ->trueLabel('Completed')
                    ->falseLabel('Pending'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesTasks::route('/'),
            'create' => Pages\CreateSalesTask::route('/create'),
            'edit' => Pages\EditSalesTask::route('/{record}/edit'),
        ];
    }
}
