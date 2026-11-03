<?php

namespace App\Filament\Resources\DeviceUnitResource\Pages;

use App\Enums\DeviceUnitStatusEnum;
use App\Filament\Resources\DeviceUnitResource;
use App\Models\DeviceUnit;
use App\Services\Inventory\UnitInventoryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDeviceUnit extends EditRecord
{
    protected static string $resource = DeviceUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn (DeviceUnit $record) => app(UnitInventoryService::class)->deleteUnit($record, auth()->id())),
        ];
    }

    /**
     * Updates pass through UnitInventoryService to adjust stock movements on status transition.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var DeviceUnit $unit */
        $unit = $record;

        $newStatus = isset($data['status'])
            ? ($data['status'] instanceof DeviceUnitStatusEnum ? $data['status'] : DeviceUnitStatusEnum::from((string) $data['status']))
            : $unit->status;

        if ($unit->status !== $newStatus) {
            app(UnitInventoryService::class)->transitionUnitStatus($unit, $newStatus, $data['notes'] ?? null, auth()->id());
        }

        $unit->update($data);

        return $unit;
    }
}
