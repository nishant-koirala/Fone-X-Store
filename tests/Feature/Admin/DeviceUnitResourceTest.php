<?php

namespace Tests\Feature\Admin;

use App\Enums\DeviceUnitStatusEnum;
use App\Filament\Resources\DeviceUnitResource\Pages\CreateDeviceUnit;
use App\Filament\Resources\DeviceUnitResource\Pages\EditDeviceUnit;
use App\Filament\Resources\DeviceUnitResource\Pages\ListDeviceUnits;
use App\Models\DeviceUnit;
use App\Models\ProductCondition;
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

        Livewire::test(CreateDeviceUnit::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'imei' => '356938039999888',
                'serial_number' => 'F2LZG899XYZ',
                'battery_health' => 91,
                'cost_price' => 45000,
                'status' => DeviceUnitStatusEnum::AVAILABLE->value,
                'notes' => 'Flawless condition, verified diagnostic',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $unit = DeviceUnit::where('imei', '356938039999888')->first();
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

    public function test_can_edit_device_unit_status(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);
        $unit = DeviceUnit::factory()->available()->create(['product_condition_id' => $condition->id]);

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

        // Stock counter should decrement via booted updated hook
        $this->assertEquals(4, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'adjustment',
            'quantity' => -1,
        ]);
    }

    public function test_deleting_available_device_unit_decrements_condition_stock(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 3]);
        $unit = DeviceUnit::factory()->available()->create(['product_condition_id' => $condition->id]);

        $unit->delete();

        $this->assertEquals(2, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'adjustment',
            'quantity' => -1,
        ]);
    }
}
