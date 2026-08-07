<?php

namespace App\Filament\Resources\TradeInValuationResource\Pages;

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
}
