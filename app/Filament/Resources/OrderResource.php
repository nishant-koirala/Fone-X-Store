<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\ProductCondition;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup(): ?string
    {
        return 'Sales & Operations';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(OrderStatusEnum::class)
                            ->required()
                            ->default(OrderStatusEnum::PENDING),

                        Forms\Components\Select::make('delivery_zone')
                            ->options([
                                'inside_valley' => 'Inside Kathmandu Valley (Pathao)',
                                'outside_valley' => 'Outside Valley (Courier)',
                            ])
                            ->default('inside_valley')
                            ->required(),

                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rs ')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Order total is read-only / computed from items.'),

                        Forms\Components\DateTimePicker::make('confirmed_at')
                            ->label('Phone Confirmation')
                            ->helperText('Timestamp of staff phone confirmation')
                            ->nullable(),
                    ])->columns(5),

                Section::make('Order Items')
                    ->description(fn (string $operation): string => $operation === 'edit'
                        ? 'Line items are locked to preserve inventory and audit trails. To modify items, cancel this order (which auto-restocks) and create a new order.'
                        : 'Select products and quantities for this order. Stock will be automatically deducted upon creation.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('product_condition_id')
                                    ->label('Product & Grade')
                                    ->options(function () {
                                        return ProductCondition::with('product')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function (ProductCondition $cond) {
                                                $prodName = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";
                                                $gradeLabel = $cond->grade->value;

                                                return [
                                                    $cond->id => "{$prodName} (Grade: {$gradeLabel}) - Rs {$cond->price} [Stock: {$cond->quantity_in_stock}]",
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->getSearchResultsUsing(fn (string $search): array => ProductCondition::with('product')
                                        ->whereHas('product', function ($query) use ($search) {
                                            $query->where('name', 'like', "%{$search}%")
                                                ->orWhere('brand', 'like', "%{$search}%");
                                        })
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function (ProductCondition $cond) {
                                            $prodName = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";
                                            $gradeLabel = $cond->grade->value;

                                            return [
                                                $cond->id => "{$prodName} (Grade: {$gradeLabel}) - Rs {$cond->price} [Stock: {$cond->quantity_in_stock}]",
                                            ];
                                        })
                                        ->toArray()
                                    )
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $cond = ProductCondition::with('product')->find($value);
                                        if (! $cond) {
                                            return null;
                                        }
                                        $prodName = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";
                                        $gradeLabel = $cond->grade->value;

                                        return "{$prodName} (Grade: {$gradeLabel}) - Rs {$cond->price} [Stock: {$cond->quantity_in_stock}]";
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($cond = ProductCondition::find($state)) {
                                            $set('price_at_purchase', $cond->price);
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(function (callable $get, string $operation) {
                                        if ($operation !== 'create') {
                                            return null;
                                        }
                                        $condId = $get('product_condition_id');
                                        if (! $condId) {
                                            return null;
                                        }
                                        $cond = ProductCondition::find($condId);

                                        return $cond ? max(1, $cond->quantity_in_stock) : null;
                                    })
                                    ->helperText(function (callable $get) {
                                        $condId = $get('product_condition_id');
                                        if (! $condId) {
                                            return null;
                                        }
                                        $cond = ProductCondition::find($condId);

                                        return $cond ? "Available in stock: {$cond->quantity_in_stock}" : null;
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('price_at_purchase')
                                    ->numeric()
                                    ->prefix('Rs ')
                                    ->required(),

                                Forms\Components\TextInput::make('cost_price_at_purchase')
                                    ->label('Cost at Sale')
                                    ->numeric()
                                    ->prefix('Rs ')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Cost price snapshotted at time of sale')
                                    ->visible(fn (string $operation): bool => $operation !== 'create'),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->deletable(fn (string $operation): bool => $operation === 'create')
                            ->addable(fn (string $operation): bool => $operation === 'create'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.phone')->label('Phone'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('delivery_zone')
                    ->label('Zone')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'outside_valley' => 'Outside Valley (Courier)',
                        default => 'Inside Valley (Pathao)',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'outside_valley' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('total')->money('NPR')->sortable(),
                Tables\Columns\TextColumn::make('gross_profit')
                    ->label('Est. Margin')
                    ->state(function (Order $record): string {
                        $profit = $record->calculateProfit();

                        return $profit !== null ? 'Rs '.number_format($profit) : '—';
                    })
                    ->color(fn (?string $state): string => match (true) {
                        $state && ! str_contains($state, '—') && ! str_contains($state, '-') => 'success',
                        $state && str_contains($state, '-') && ! str_contains($state, '—') => 'danger',
                        default => 'gray',
                    })
                    ->tooltip('Total revenue minus purchase cost of inventory'),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Phone Verified')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Confirmed' : 'Awaiting Call')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatusEnum::class),
                Tables\Filters\SelectFilter::make('delivery_zone')
                    ->options([
                        'inside_valley' => 'Inside Kathmandu Valley (Pathao)',
                        'outside_valley' => 'Outside Valley (Courier)',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Action::make('confirmPhone')
                    ->label('Confirm Phone')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->confirmed_at === null && $record->status === OrderStatusEnum::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Order by Phone')
                    ->modalDescription('Mark this COD order as confirmed with the customer. Confirmed orders are protected from automatic expiry.')
                    ->action(function (Order $record): void {
                        $record->update(['confirmed_at' => now()]);

                        Notification::make()
                            ->title('Order Confirmed')
                            ->body("Order #{$record->id} confirmed with customer.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                RestoreAction::make(),
                DeleteAction::make()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::CANCELLED)
                    ->tooltip('Only cancelled orders can be deleted. Cancel the order first to auto-restock inventory.'),
                ForceDeleteAction::make()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::CANCELLED)
                    ->tooltip('Only cancelled orders can be permanently deleted.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
