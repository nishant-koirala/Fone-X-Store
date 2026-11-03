<?php

namespace Tests\Feature\Inventory;

use App\Actions\Checkout\PlaceOrder;
use App\Enums\DeviceUnitStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\DeviceUnit;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Rules\Imei;
use App\Services\Inventory\UnitInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UnitLinkageTest extends TestCase
{
    use RefreshDatabase;

    protected UnitInventoryService $unitService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->unitService = app(UnitInventoryService::class);
    }

    public function test_tracks_units_toggle_validation(): void
    {
        // 1. Condition has stock 2, but 0 available units -> cannot enable
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 2,
            'tracks_units' => false,
        ]);

        $this->expectException(ValidationException::class);
        $condition->update(['tracks_units' => true]);
    }

    public function test_tracks_units_toggle_allows_activation_when_units_match_stock(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 2,
            'tracks_units' => false,
        ]);

        // Register 2 available units to match stock
        DeviceUnit::factory()->count(2)->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        $condition->update(['tracks_units' => true]);
        $this->assertTrue($condition->fresh()->tracks_units);

        // Turning it off is always allowed
        $condition->update(['tracks_units' => false]);
        $this->assertFalse($condition->fresh()->tracks_units);
    }

    public function test_selling_auto_assigns_oldest_available_units(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 50000,
            'cost_price' => 40000,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        $u1 = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'imei' => Imei::fake(),
            'cost_price' => 41000,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'created_at' => now()->subDay(),
        ]);
        $u2 = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'imei' => Imei::fake(),
            'cost_price' => 42000,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'created_at' => now(),
        ]);

        $action = app(PlaceOrder::class);
        $order = $action->execute(
            data: [
                'name' => 'Hari Thapa',
                'phone' => '9841000000',
                'address' => 'Patan, Lalitpur',
            ],
            cartSession: [
                $condition->id => ['quantity' => 1],
            ]
        );

        $this->assertNotNull($order);
        $this->assertSame(1, $condition->fresh()->quantity_in_stock);

        // Oldest unit (u1) should have been assigned and marked sold
        $freshU1 = $u1->fresh();
        $this->assertSame(DeviceUnitStatusEnum::SOLD, $freshU1->status);
        $this->assertNotNull($freshU1->order_item_id);
        $this->assertNotNull($freshU1->sold_at);

        // Newer unit (u2) remains available
        $this->assertSame(DeviceUnitStatusEnum::AVAILABLE, $u2->fresh()->status);
    }

    public function test_concurrent_purchase_of_last_unit_fails_for_second_customer(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 80000,
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);

        $unit = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'imei' => Imei::fake(),
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        $action = app(PlaceOrder::class);

        // First purchase succeeds
        $order1 = $action->execute(
            data: ['name' => 'Customer 1', 'phone' => '9841111111', 'address' => 'Kathmandu'],
            cartSession: [$condition->id => ['quantity' => 1]]
        );
        $this->assertNotNull($order1);
        $this->assertSame(0, $condition->fresh()->quantity_in_stock);
        $this->assertSame(DeviceUnitStatusEnum::SOLD, $unit->fresh()->status);

        // Second purchase with stale cart attempt must fail with InsufficientStockException
        $this->expectException(InsufficientStockException::class);
        $action->execute(
            data: ['name' => 'Customer 2', 'phone' => '9841222222', 'address' => 'Pokhara'],
            cartSession: [$condition->id => ['quantity' => 1]]
        );
    }

    public function test_cancelling_order_returns_exact_same_units_to_available(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 60000,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        $u1 = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);
        $u2 = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        $action = app(PlaceOrder::class);
        $order = $action->execute(
            data: ['name' => 'Test Customer', 'phone' => '9841333333', 'address' => 'Bhaktapur'],
            cartSession: [$condition->id => ['quantity' => 1]]
        );

        $assignedUnit = DeviceUnit::where('order_item_id', $order->items->first()->id)->first();
        $this->assertNotNull($assignedUnit);
        $this->assertSame(DeviceUnitStatusEnum::SOLD, $assignedUnit->status);
        $this->assertSame(1, $condition->fresh()->quantity_in_stock);

        // Cancel order
        $order->update(['status' => OrderStatusEnum::CANCELLED]);

        // Exact unit is back to available
        $freshUnit = $assignedUnit->fresh();
        $this->assertSame(DeviceUnitStatusEnum::AVAILABLE, $freshUnit->status);
        $this->assertNull($freshUnit->order_item_id);
        $this->assertNull($freshUnit->sold_at);

        // Condition stock restored
        $this->assertSame(2, $condition->fresh()->quantity_in_stock);
    }

    public function test_expiry_command_returns_units_to_available(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 70000,
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);

        $unit = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        $action = app(PlaceOrder::class);
        $order = $action->execute(
            data: ['name' => 'Stale Buyer', 'phone' => '9841444444', 'address' => 'Kathmandu'],
            cartSession: [$condition->id => ['quantity' => 1]]
        );

        $this->assertSame(DeviceUnitStatusEnum::SOLD, $unit->fresh()->status);
        $this->assertSame(0, $condition->fresh()->quantity_in_stock);

        // Backdate order to 50 hours ago with confirmed_at null
        $order->forceFill([
            'confirmed_at' => null,
            'created_at' => now()->subHours(50),
        ])->saveQuietly();

        // Run expiry command
        $this->artisan('shop:expire-unconfirmed-orders')
            ->assertSuccessful();

        $this->assertSame(OrderStatusEnum::CANCELLED, $order->fresh()->status);
        $this->assertSame(DeviceUnitStatusEnum::AVAILABLE, $unit->fresh()->status);
        $this->assertNull($unit->fresh()->order_item_id);
        $this->assertSame(1, $condition->fresh()->quantity_in_stock);
    }

    public function test_mixed_cart_tracked_and_untracked(): void
    {
        $phoneProd = Product::factory()->create(['is_visible' => true]);
        $trackedPhone = ProductCondition::factory()->create([
            'product_id' => $phoneProd->id,
            'price' => 90000,
            'cost_price' => 75000,
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);
        $phoneUnit = DeviceUnit::factory()->create([
            'product_condition_id' => $trackedPhone->id,
            'cost_price' => 74000,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);

        $caseProd = Product::factory()->create(['is_visible' => true]);
        $untrackedCase = ProductCondition::factory()->create([
            'product_id' => $caseProd->id,
            'price' => 2000,
            'cost_price' => 1000,
            'quantity_in_stock' => 10,
            'tracks_units' => false,
        ]);

        $action = app(PlaceOrder::class);
        $order = $action->execute(
            data: ['name' => 'Mixed Buyer', 'phone' => '9841555555', 'address' => 'Kathmandu'],
            cartSession: [
                $trackedPhone->id => ['quantity' => 1],
                $untrackedCase->id => ['quantity' => 2],
            ]
        );

        $this->assertNotNull($order);
        $this->assertSame(0, $trackedPhone->fresh()->quantity_in_stock);
        $this->assertSame(8, $untrackedCase->fresh()->quantity_in_stock);
        $this->assertSame(DeviceUnitStatusEnum::SOLD, $phoneUnit->fresh()->status);

        // Tracked item has cost from unit (74000)
        $phoneItem = $order->items()->where('product_condition_id', $trackedPhone->id)->first();
        $this->assertEquals(74000.0, (float) $phoneItem->cost_price_at_purchase);

        // Untracked item has cost from condition (1000)
        $caseItem = $order->items()->where('product_condition_id', $untrackedCase->id)->first();
        $this->assertEquals(1000.0, (float) $caseItem->cost_price_at_purchase);
    }

    public function test_unit_creation_and_status_changes_keep_stock_counter_accurate(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);

        // 1. Create unit as available -> stock +1
        $unit = $this->unitService->registerUnit($condition, [
            'imei' => Imei::fake(),
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);
        $this->assertSame(6, $condition->fresh()->quantity_in_stock);

        // 2. Mark unit defective -> stock -1
        $this->unitService->transitionUnitStatus($unit, DeviceUnitStatusEnum::DEFECTIVE);
        $this->assertSame(5, $condition->fresh()->quantity_in_stock);

        // 3. Restore unit to available -> stock +1
        $this->unitService->transitionUnitStatus($unit, DeviceUnitStatusEnum::AVAILABLE);
        $this->assertSame(6, $condition->fresh()->quantity_in_stock);

        // 4. Delete available unit -> stock -1
        $this->unitService->deleteUnit($unit);
        $this->assertSame(5, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('device_units', ['id' => $unit->id]);
    }

    public function test_cannot_delete_sold_unit(): void
    {
        $condition = ProductCondition::factory()->create();
        $unit = DeviceUnit::factory()->sold()->create(['product_condition_id' => $condition->id]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot delete a sold device unit');

        $this->unitService->deleteUnit($unit);
    }

    public function test_mark_returned_and_return_to_stock_workflow(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 1]);
        $unit = DeviceUnit::factory()->sold()->create(['product_condition_id' => $condition->id]);

        // 1. Mark returned -> status returned, does NOT increment stock
        $this->unitService->markReturned($unit, 'Screen flickering under guarantee');
        $this->assertSame(DeviceUnitStatusEnum::RETURNED, $unit->fresh()->status);
        $this->assertStringContainsString('Screen flickering', $unit->fresh()->notes);
        $this->assertSame(1, $condition->fresh()->quantity_in_stock);

        // 2. Return to stock -> status available, increments stock +1
        $this->unitService->returnToStock($unit);
        $this->assertSame(DeviceUnitStatusEnum::AVAILABLE, $unit->fresh()->status);
        $this->assertSame(2, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 1,
        ]);
    }
}
