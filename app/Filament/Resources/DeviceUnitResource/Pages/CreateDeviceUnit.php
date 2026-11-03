<?php

namespace App\Filament\Resources\DeviceUnitResource\Pages;

use App\Filament\Resources\DeviceUnitResource;
use App\Models\ProductCondition;
use App\Services\Inventory\UnitInventoryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDeviceUnit extends CreateRecord
{
    protected static string $resource = DeviceUnitResource::class;

    /**
     * Creation delegates to UnitInventoryService to record +1 stock movement when status is available.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $condition = ProductCondition::findOrFail($data['product_condition_id']);

        return app(UnitInventoryService::class)->registerUnit($condition, $data, auth()->id());
    }
}
