<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Department;
use App\Models\Reservation;
use App\Services\BillingService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class QuickPostingPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.quick-posting';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Bolt;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Quick Posting';

    protected static ?string $title = 'Quick Posting to Guest Bill';

    protected static ?string $slug = 'quick-posting';

    public ?int $selectedDepartment = null;

    public ?int $selectedArticle = null;

    /** @var array<int, array{room_no: string, qty: int, price: float}> */
    public array $postingLines = [];

    public string $postingStatus = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('quick_post')
                ->label('Quick Post')
                ->icon(Heroicon::Bolt)
                ->color('success')
                ->form([
                    Select::make('department_id')
                        ->label('Department')
                        ->options(Department::active()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->helperText('Select the department for the article.'),
                    Select::make('article_id')
                        ->label('Article')
                        ->options(fn (callable $get) => $get('department_id')
                            ? Article::query()
                                ->active()
                                ->sales()
                                ->where('department_id', $get('department_id'))
                                ->get()
                                ->mapWithKeys(fn (Article $a) => [$a->id => "[{$a->article_no}] {$a->name} — Rp " . number_format((float) $a->default_price, 0, ',', '.')])
                            : [])
                        ->searchable()
                        ->required()
                        ->helperText('Select the article to post.'),
                    Repeater::make('rooms')
                        ->label('Rooms to Post')
                        ->schema([
                            Select::make('reservation_id')
                                ->label('Room / Guest')
                                ->options(
                                    Reservation::query()
                                        ->where('status', ReservationStatus::CheckedIn)
                                        ->with(['guest', 'room'])
                                        ->get()
                                        ->mapWithKeys(fn (Reservation $r) => [
                                            $r->id => "Rm {$r->room?->room_number} — {$r->guest?->full_name}",
                                        ])
                                )
                                ->searchable()
                                ->required(),
                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                            TextInput::make('price')
                                ->label('Price')
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Leave empty for default price.'),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel('Add Room'),
                ])
                ->action(function (array $data) {
                    $article = Article::findOrFail($data['article_id']);
                    $billing = app(BillingService::class);
                    $posted = 0;

                    foreach ($data['rooms'] as $line) {
                        $reservation = Reservation::findOrFail($line['reservation_id']);
                        $rate = ! empty($line['price']) ? (float) $line['price'] : null;
                        $billing->quickPost($reservation, $article, (int) $line['qty'], $rate);
                        $posted++;
                    }

                    Notification::make()
                        ->title("Posted {$article->name} to {$posted} room(s)")
                        ->success()
                        ->send();
                }),
        ];
    }
}
