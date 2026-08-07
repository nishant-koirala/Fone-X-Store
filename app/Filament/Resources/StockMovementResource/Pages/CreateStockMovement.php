<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\ProductCondition;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $condition = ProductCondition::findOrFail($data['product_condition_id']);

        return $condition->recordStockMovement(
            type: $data['type'],
            quantity: (int) $data['quantity'],
            note: $data['note'] ?? null,
            createdBy: auth()->id()
        );
    }
}
