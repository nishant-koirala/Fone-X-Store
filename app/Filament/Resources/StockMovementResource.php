<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\ProductCondition;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    public static function getNavigationIcon(): ?string { return 'heroicon-o-truck'; }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory Management';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_condition_id')
                    ->label('Product Listing & Grade')
                    ->options(function () {
                        return ProductCondition::with('product')
                            ->get()
                            ->mapWithKeys(fn ($cond) => [
                                $cond->id => "{$cond->product->brand} {$cond->product->name} (Grade: {$cond->grade}) - Current Stock: {$cond->quantity_in_stock}",
                            ]);
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('type')
                    ->options([
                        'restock' => 'Restock (+ Stock)',
                        'sale' => 'Sale (- Stock)',
                        'exchange_in' => 'Exchange In (+ Stock)',
                        'adjustment' => 'Adjustment (+/- Stock)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->helperText('Use positive integer to add stock (e.g. 10), or negative to decrease stock (e.g. -2 for sale/damage).')
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('productCondition.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productCondition.grade')
                    ->label('Grade')
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'restock' => 'success',
                        'exchange_in' => 'info',
                        'sale' => 'danger',
                        'adjustment' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Logged By')
                    ->default('System'),
                Tables\Columns\TextColumn::make('note')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'restock' => 'Restock',
                        'sale' => 'Sale',
                        'exchange_in' => 'Exchange In',
                        'adjustment' => 'Adjustment',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
        ];
    }
}
