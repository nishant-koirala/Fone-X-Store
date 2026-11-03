<?php

namespace App\Filament\Resources;

use App\Enums\TradeInStatusEnum;
use App\Filament\Resources\TradeInValuationResource\Pages;
use App\Models\ProductCondition;
use App\Models\TradeInValuation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TradeInValuationResource extends Resource
{
    protected static ?string $model = TradeInValuation::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-arrows-right-left';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales & Operations';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    protected static ?string $navigationLabel = 'Trade-In Valuations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('device_brand')
                    ->required(),

                Forms\Components\TextInput::make('device_model')
                    ->required(),

                Forms\Components\TextInput::make('estimated_value')
                    ->numeric()
                    ->prefix('Rs ')
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->options(TradeInStatusEnum::class)
                    ->required()
                    ->default(TradeInStatusEnum::PENDING),

                Forms\Components\Select::make('product_condition_id')
                    ->label('Target Inventory Condition')
                    ->relationship('productCondition')
                    ->options(function () {
                        return ProductCondition::with('product')
                            ->get()
                            ->mapWithKeys(function (ProductCondition $cond) {
                                $prodName = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";

                                return [$cond->id => "{$prodName} (Grade: {$cond->grade->value}) - Stock: {$cond->quantity_in_stock}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->helperText('Assign accepted device to a sellable product condition grade')
                    ->nullable(),

                Forms\Components\Textarea::make('condition_description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->default('Guest')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('device_brand')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('device_model')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->prefix('Rs ')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Unassessed'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock_ingested_at')
                    ->label('Stock Ingestion')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'In Stock (+1)' : 'Not Ingested')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(TradeInStatusEnum::class),
            ])
            ->actions([
                Action::make('ingestStock')
                    ->label('Ingest to Stock')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (TradeInValuation $record): bool => $record->stock_ingested_at === null)
                    ->form([
                        Forms\Components\Select::make('product_condition_id')
                            ->label('Select Condition to Ingest Into (+1 Stock)')
                            ->options(function () {
                                return ProductCondition::with('product')
                                    ->get()
                                    ->mapWithKeys(function (ProductCondition $cond) {
                                        $prodName = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";

                                        return [$cond->id => "{$prodName} (Grade: {$cond->grade->value}) - Current: {$cond->quantity_in_stock}"];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('imei')
                            ->label('Device IMEI (15 digits)')
                            ->maxLength(25)
                            ->placeholder('e.g. 356938031234567')
                            ->helperText('IMEI for NTA/MDMS registration and returns tracking')
                            ->nullable(),

                        Forms\Components\TextInput::make('battery_health')
                            ->label('Tested Battery Health %')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->nullable(),
                    ])
                    ->action(function (TradeInValuation $record, array $data): void {
                        $condition = ProductCondition::findOrFail($data['product_condition_id']);
                        $imei = ! empty($data['imei']) ? trim($data['imei']) : null;
                        $batteryHealth = ! empty($data['battery_health']) ? (int) $data['battery_health'] : null;

                        $record->ingestIntoStock($condition, auth()->id(), $imei, $batteryHealth);

                        Notification::make()
                            ->title('Device Ingested Into Inventory')
                            ->body("Added 1 unit to {$condition->product?->name} ({$condition->grade->value}) with IMEI tracking.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradeInValuations::route('/'),
            'create' => Pages\CreateTradeInValuation::route('/create'),
            'edit' => Pages\EditTradeInValuation::route('/{record}/edit'),
        ];
    }
}
