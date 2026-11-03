<?php

namespace Tests\Feature\Admin;

use App\Enums\DeviceUnitStatusEnum;
use App\Filament\Resources\DeviceUnitResource\Pages\CreateDeviceUnit;
use App\Filament\Resources\DeviceUnitResource\Pages\EditDeviceUnit;
use App\Filament\Resources\DeviceUnitResource\Pages\ListDeviceUnits;
use App\Models\DeviceUnit;
use App\Models\ProductCondition;
use App\Rules\Imei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeviceUnitResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_list_device_units_in_admin(): void
    {
        $units = DeviceUnit::factory()->count(3)->create();

        Livewire::test(ListDeviceUnits::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($units);
    }

    public function test_can_create_device_unit_with_imei_and_battery_health(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 2]);
        $validImei = Imei::fake();

        Livewire::test(CreateDeviceUnit::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'imei' => $validImei,
                'serial_number' => 'F2LZG899XYZ',
                'battery_health' => 91,
                'cost_price' => 45000,
                'status' => DeviceUnitStatusEnum::AVAILABLE->value,
                'notes' => 'Flawless condition, verified diagnostic',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $unit = DeviceUnit::where('imei', $validImei)->first();
        $this->assertNotNull($unit);
        $this->assertEquals(91, $unit->battery_health);
        $this->assertEquals(45000.0, (float) $unit->cost_price);

        // Stock counter should increment via the afterCreate hook
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 1,
        ]);
    }

    public function test_rejects_invalid_luhn_imei_and_duplicate_imei(): void
    {
        $condition = ProductCondition::factory()->create();
        $existing = DeviceUnit::factory()->create(['imei' => Imei::fake()]);

        // 1. Invalid Luhn check digit
        Livewire::test(CreateDeviceUnit::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'imei' => '123456789012345',
            ])
            ->call('create')
            ->assertHasFormErrors(['imei']);

        // 2. Duplicate IMEI
        Livewire::test(CreateDeviceUnit::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'imei' => $existing->imei,
            ])
            ->call('create')
            ->assertHasFormErrors(['imei']);
    }

    public function test_can_edit_device_unit_status(): void
    {
        $unit = DeviceUnit::factory()->available()->create();

        Livewire::test(EditDeviceUnit::class, ['record' => $unit->getRouteKey()])
            ->fillForm([
                'status' => DeviceUnitStatusEnum::DEFECTIVE->value,
                'notes' => 'Faulty camera sensor identified during diagnostic',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $unit->fresh();
        $this->assertEquals(DeviceUnitStatusEnum::DEFECTIVE, $fresh->status);
        $this->assertStringContainsString('Faulty camera', $fresh->notes);
    }

    public function test_can_mark_sold_unit_returned_via_filament_action(): void
    {
        $unit = DeviceUnit::factory()->sold()->create();

        Livewire::test(ListDeviceUnits::class)
            ->callTableAction('markReturned', $unit, [
                'return_reason' => 'Customer reported microphone defect within 7 days',
            ])
            ->assertHasNoTableActionErrors();

        $fresh = $unit->fresh();
        $this->assertEquals(DeviceUnitStatusEnum::RETURNED, $fresh->status);
        $this->assertStringContainsString('microphone defect', $fresh->notes);
    }

    public function test_can_return_unit_to_stock_via_filament_action(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 1]);
        $unit = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::RETURNED,
        ]);

        Livewire::test(ListDeviceUnits::class)
            ->callTableAction('returnToStock', $unit)
            ->assertHasNoTableActionErrors();

        $this->assertEquals(DeviceUnitStatusEnum::AVAILABLE, $unit->fresh()->status);
        $this->assertEquals(2, $condition->fresh()->quantity_in_stock);
    }

    public function test_delete_available_unit_decrements_stock(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 4]);
        $unit = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        Livewire::test(ListDeviceUnits::class)
            ->callTableAction('delete', $unit)
            ->assertHasNoTableActionErrors();

        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('device_units', ['id' => $unit->id]);
    }
}
