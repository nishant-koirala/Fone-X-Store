<?php

namespace App\Filament\Resources\DeviceUnitResource\Pages;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Filament\Resources\DeviceUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeviceUnit extends CreateRecord
{
    protected static string $resource = DeviceUnitResource::class;

    protected function afterCreate(): void
    {
        $unit = $this->record;

        if ($unit->status === DeviceUnitStatusEnum::AVAILABLE && $unit->productCondition) {
            $unit->productCondition->recordStockMovement(
                type: StockMovementTypeEnum::RESTOCK,
                quantity: 1,
                note: "New device unit registered (IMEI: " . ($unit->imei ?? 'N/A') . ")",
                createdBy: auth()->id()
            );
        }
    }
}
