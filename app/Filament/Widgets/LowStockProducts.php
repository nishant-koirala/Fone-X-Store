<?php

namespace App\Filament\Widgets;

use App\Models\ProductCondition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alert (< 5 items in stock)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductCondition::query()
                    ->with(['product', 'product.category'])
                    ->where('quantity_in_stock', '<', 5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.brand')
                    ->label('Brand')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Condition Grade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'success',
                        'A' => 'info',
                        'B' => 'warning',
                        'C' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_in_stock')
                    ->label('Quantity in Stock')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning')
                    ->sortable(),
            ]);
    }
}
