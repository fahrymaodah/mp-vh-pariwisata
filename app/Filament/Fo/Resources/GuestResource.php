<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources;

use App\Enums\Gender;
use App\Enums\GuestType;
use App\Filament\Fo\Resources\GuestResource\Pages;
use App\Filament\Fo\Resources\GuestResource\RelationManagers;
use App\Models\Guest;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Users;

    protected static string | UnitEnum | null $navigationGroup = 'Reception';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Guest Card Files';

    protected static ?string $modelLabel = 'Guest';

    protected static ?string $pluralModelLabel = 'Guest Card Files';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Guest Card')
                    ->tabs([
                        Tab::make('Guest Information')
                            ->icon(Heroicon::User)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('type')
                                            ->options(GuestType::class)
                                            ->default(GuestType::Individual)
                                            ->required()
                                            ->live()
                                            ->columnSpan(1),
                                        TextInput::make('guest_no')
                                            ->label('Guest No')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('Auto-generated')
                                            ->columnSpan(1),
                                        Toggle::make('is_vip')
                                            ->label('VIP')
                                            ->inline(false)
                                            ->columnSpan(1),
                                    ]),

                                Section::make('Personal Details')
                                    ->visible(fn ($get) => $get('type') === GuestType::Individual->value || $get('type') === GuestType::Individual)
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('title')
                                                    ->options([
                                                        'Mr' => 'Mr',
                                                        'Mrs' => 'Mrs',
                                                        'Ms' => 'Ms',
                                                        'Dr' => 'Dr',
                                                        'Prof' => 'Prof',
                                                    ])
                                                    ->columnSpan(1),
                                                TextInput::make('first_name')
                                                    ->label('First Name')
                                                    ->maxLength(255)
                                                    ->columnSpan(1),
                                                TextInput::make('name')
                                                    ->label('Last Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(2),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('sex')
                                                    ->label('Gender')
                                                    ->options(Gender::class),
                                                DatePicker::make('birth_date')
                                                    ->label('Birth Date'),
                                                TextInput::make('birth_place')
                                                    ->label('Birth Place')
                                                    ->maxLength(100),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('id_card_no')
                                                    ->label('ID Card No')
                                                    ->maxLength(50)
                                                    ->placeholder('Passport / KTP / etc'),
                                                DatePicker::make('expired_date')
                                                    ->label('Expired Date'),
                                            ]),
                                    ]),

                                Section::make('Company Details')
                                    ->visible(fn ($get) => $get('type') === GuestType::Company->value || $get('type') === GuestType::Company)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Company Name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('company_title')
                                                    ->label('Company Title')
                                                    ->maxLength(50)
                                                    ->placeholder('PT, CV, Ltd, etc'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('expired_date')
                                                    ->label('Contract Expired Date'),
                                                TextInput::make('payment_terms')
                                                    ->label('Payment Terms')
                                                    ->maxLength(100),
                                            ]),
                                    ]),

                                Section::make('Travel Agent Details')
                                    ->visible(fn ($get) => $get('type') === GuestType::TravelAgent->value || $get('type') === GuestType::TravelAgent)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Travel Agent Name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('company_title')
                                                    ->label('Company Title')
                                                    ->maxLength(50),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('expired_date')
                                                    ->label('Contract Expired Date'),
                                                TextInput::make('payment_terms')
                                                    ->label('Payment Terms')
                                                    ->maxLength(100),
                                            ]),
                                    ]),

                                Section::make('Address & Contact')
                                    ->schema([
                                        Textarea::make('address')
                                            ->rows(2),
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('city')
                                                    ->maxLength(100),
                                                TextInput::make('zip')
                                                    ->label('Zip Code')
                                                    ->maxLength(20),
                                                TextInput::make('country')
                                                    ->maxLength(10)
                                                    ->placeholder('Country code'),
                                                TextInput::make('nationality')
                                                    ->maxLength(10)
                                                    ->placeholder('Nation code'),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->maxLength(50),
                                                TextInput::make('fax')
                                                    ->maxLength(50),
                                                TextInput::make('email')
                                                    ->email()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Financial & Sales')
                            ->icon(Heroicon::CurrencyDollar)
                            ->schema([
                                Section::make('Financial')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('credit_limit')
                                                    ->label('Credit Limit')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->default(0),
                                                TextInput::make('discount')
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->maxValue(100),
                                                TextInput::make('price_code')
                                                    ->label('Price Code')
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                                Section::make('Sales & Segment')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('master_company_id')
                                                    ->label('Master Company')
                                                    ->relationship(
                                                        'masterCompany',
                                                        'name',
                                                        fn (Builder $query) => $query->where('type', GuestType::Company)
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Select company'),
                                                Select::make('main_segment_id')
                                                    ->label('Main Segment')
                                                    ->relationship('mainSegment', 'description', fn (Builder $query) => $query->active())
                                                    ->searchable()
                                                    ->preload(),
                                                Select::make('sales_user_id')
                                                    ->label('Sales ID')
                                                    ->relationship('salesUser', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('source_booking')
                                                    ->label('Source of Booking')
                                                    ->maxLength(100),
                                                Toggle::make('is_blacklisted')
                                                    ->label('Blacklisted')
                                                    ->inline(false),
                                            ]),
                                        Textarea::make('comments')
                                            ->rows(3),
                                    ]),
                            ]),

                        Tab::make('Photo')
                            ->icon(Heroicon::Camera)
                            ->schema([
                                Section::make('Guest Photo')
                                    ->schema([
                                        FileUpload::make('photo_path')
                                            ->label('Photo')
                                            ->image()
                                            ->imageEditor()
                                            ->directory('guests/photos')
                                            ->maxSize(2048)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_no')
                    ->label('Guest No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Guest $record): string => $record->type === GuestType::Individual
                        ? ($record->first_name ?? '')
                        : ($record->company_title ?? '')),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mainSegment.description')
                    ->label('Segment')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->trueColor('warning'),
                Tables\Columns\IconColumn::make('has_membership')
                    ->label('Member')
                    ->state(fn (Guest $record): bool => $record->memberships()->where('is_active', true)->exists())
                    ->boolean()
                    ->trueIcon(Heroicon::CheckBadge)
                    ->trueColor('success'),
                Tables\Columns\TextColumn::make('expired_date')
                    ->label('Expired')
                    ->date()
                    ->color(fn (Guest $record): ?string => self::getExpiredDateColor($record))
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(GuestType::class)
                    ->label('Card Type'),
                Tables\Filters\SelectFilter::make('main_segment_id')
                    ->relationship('mainSegment', 'description')
                    ->label('Segment')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_vip')
                    ->label('VIP Only'),
                Tables\Filters\TernaryFilter::make('is_blacklisted')
                    ->label('Blacklisted'),
                Tables\Filters\Filter::make('has_membership')
                    ->label('Has Active Membership')
                    ->query(fn (Builder $query) => $query->whereHas('memberships', fn (Builder $q) => $q->where('is_active', true))),
                Tables\Filters\Filter::make('expired_soon')
                    ->label('Contract Expiring ≤30 days')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('expired_date')
                        ->where('expired_date', '<=', now()->addDays(30))
                        ->where('expired_date', '>=', now())),
                Tables\Filters\Filter::make('expired_already')
                    ->label('Contract Expired')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('expired_date')
                        ->where('expired_date', '<', now())),
            ])
            ->actions([
                Actions\ViewAction::make(),
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
            RelationManagers\ContactsRelationManager::class,
            RelationManagers\SegmentsRelationManager::class,
            RelationManagers\MembershipsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            'view' => Pages\ViewGuest::route('/{record}'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
        ];
    }

    /**
     * Expired date color indicator:
     * - Red: already expired
     * - Yellow/Warning: expiring within 30 days
     * - null: ok
     */
    protected static function getExpiredDateColor(Guest $record): ?string
    {
        if (! $record->expired_date) {
            return null;
        }

        if ($record->expired_date->isPast()) {
            return 'danger';
        }

        if ($record->expired_date->diffInDays(now()) <= 30) {
            return 'warning';
        }

        return null;
    }
}
