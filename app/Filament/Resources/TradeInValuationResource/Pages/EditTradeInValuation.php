<?php

namespace App\Filament\Resources\TradeInValuationResource\Pages;

use App\Enums\TradeInStatusEnum;
use App\Filament\Resources\TradeInValuationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTradeInValuation extends EditRecord
{
    protected static string $resource = TradeInValuationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->stock_ingested_at === null
            && $record->status === TradeInStatusEnum::COMPLETED
            && $record->product_condition_id) {
            $record->loadMissing('productCondition');
            if ($record->productCondition) {
                $record->ingestIntoStock($record->productCondition);
            }
        }
    }
}
