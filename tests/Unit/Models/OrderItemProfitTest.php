<?php

namespace Tests\Unit\Models;

use App\Actions\Checkout\PlaceOrder;
use App\Enums\DeviceUnitStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\DeviceUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Rules\Imei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderItemProfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_untracked_qty_3_calculates_profit_correctly(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 1000.00,
            'cost_price' => 600.00,
            'quantity_in_stock' => 10,
            'tracks_units' => false,
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Untracked Customer',
                'phone' => '9841234567',
                'address' => 'Kathmandu',
            ],
            cartSession: [
                $condition->id => ['quantity' => 3],
            ]
        );

        $item = $order->items->first();
        $this->assertEquals(600.00, (float) $item->cost_price_at_purchase);
        $this->assertEquals(1800.00, (float) $item->cost_total_at_purchase);

        // Revenue: 1,000 * 3 = 3,000. Cost Total: 1,800. Profit: 1,200.
        $this->assertEquals(1200.00, $item->profit);
        $this->assertEquals(1200.00, $order->calculateProfit());
    }

    public function test_tracked_qty_3_with_different_unit_costs_calculates_profit_from_summed_cost(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 50000.00,
            'quantity_in_stock' => 3,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 30000.00,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 32000.00,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 34000.00,
            'imei' => Imei::fake(),
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Tracked Customer',
                'phone' => '9841234567',
                'address' => 'Patan',
            ],
            cartSession: [
                $condition->id => ['quantity' => 3],
            ]
        );

        $item = $order->items->first();
        // Sum: 30000 + 32000 + 34000 = 96000
        $this->assertEquals(96000.00, (float) $item->cost_total_at_purchase);
        // Average: 96000 / 3 = 32000
        $this->assertEquals(32000.00, (float) $item->cost_price_at_purchase);

        // Revenue: 50,000 * 3 = 150,000. Cost: 96,000. Profit: 54,000
        $this->assertEquals(54000.00, $item->profit);
        $this->assertEquals(54000.00, $order->calculateProfit());
    }

    public function test_mixed_cart_calculates_profit_correctly_across_tracked_and_untracked_items(): void
    {
        $product1 = Product::factory()->create();
        $trackedCondition = ProductCondition::factory()->create([
            'product_id' => $product1->id,
            'price' => 50000.00,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $trackedCondition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 35000.00,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $trackedCondition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 40000.00,
            'imei' => Imei::fake(),
        ]);

        $product2 = Product::factory()->create();
        $untrackedCondition = ProductCondition::factory()->create([
            'product_id' => $product2->id,
            'price' => 1000.00,
            'cost_price' => 600.00,
            'quantity_in_stock' => 10,
            'tracks_units' => false,
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Mixed Cart Customer',
                'phone' => '9841234567',
                'address' => 'Baneshwor, Kathmandu',
            ],
            cartSession: [
                $trackedCondition->id => ['quantity' => 2],
                $untrackedCondition->id => ['quantity' => 3],
            ]
        );

        $trackedItem = $order->items->firstWhere('product_condition_id', $trackedCondition->id);
        $untrackedItem = $order->items->firstWhere('product_condition_id', $untrackedCondition->id);

        // Tracked item: Cost total = 75,000. Cost per-unit = 37,500. Profit = 100,000 - 75,000 = 25,000
        $this->assertEquals(75000.00, (float) $trackedItem->cost_total_at_purchase);
        $this->assertEquals(37500.00, (float) $trackedItem->cost_price_at_purchase);
        $this->assertEquals(25000.00, $trackedItem->profit);

        // Untracked item: Unit Cost = 600, Total Cost = 1,800. Profit = 3,000 - 1,800 = 1,200
        $this->assertEquals(600.00, (float) $untrackedItem->cost_price_at_purchase);
        $this->assertEquals(1800.00, (float) $untrackedItem->cost_total_at_purchase);
        $this->assertEquals(1200.00, $untrackedItem->profit);

        // Combined Order Profit: 25,000 + 1,200 = 26,200
        $this->assertEquals(26200.00, $order->calculateProfit());
    }

    public function test_tracked_order_with_null_cost_unit_sets_cost_null_and_profit_null(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 30000.00,
            'cost_price' => null, // No condition fallback cost
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 20000.00,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => null, // Missing cost
            'imei' => Imei::fake(),
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Null Cost Customer',
                'phone' => '9841234567',
                'address' => 'Bhaktapur',
            ],
            cartSession: [
                $condition->id => ['quantity' => 2],
            ]
        );

        $item = $order->items->first();
        $this->assertNull($item->cost_price_at_purchase);
        $this->assertNull($item->cost_total_at_purchase);
        $this->assertNull($item->profit);
        $this->assertNull($order->calculateProfit());
    }

    public function test_old_rows_with_null_cost_total_still_compute_profit_using_per_unit_formula(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'price_at_purchase' => 1000.00,
            'cost_price_at_purchase' => 600.00,
            'cost_total_at_purchase' => null, // Legacy row without cost_total_at_purchase
        ]);

        // (1000 - 600) * 3 = 1200
        $this->assertEquals(1200.00, $item->profit);
        $this->assertEquals(1200.00, $order->calculateProfit());
    }

    public function test_cancelled_and_restocked_order_preserves_profit_and_costs_without_linked_units(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 60000.00,
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);

        $unit = DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 42000.00,
            'imei' => Imei::fake(),
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Cancel Customer',
                'phone' => '9841234567',
                'address' => 'Kathmandu',
            ],
            cartSession: [
                $condition->id => ['quantity' => 1],
            ]
        );

        $item = $order->items->first();
        $this->assertEquals(42000.00, (float) $item->cost_total_at_purchase);
        $this->assertEquals(18000.00, $item->profit);

        // Cancel the order (restocks device unit)
        $order->update(['status' => OrderStatusEnum::CANCELLED]);

        // Device unit is restocked to available and unlinked from order item
        $this->assertSame(DeviceUnitStatusEnum::AVAILABLE, $unit->fresh()->status);
        $this->assertNull($unit->fresh()->order_item_id);

        // Order item profit calculation remains valid because it relies on snapshot columns, not linked units
        $freshItem = $item->fresh();
        $this->assertEquals(42000.00, (float) $freshItem->cost_total_at_purchase);
        $this->assertEquals(18000.00, $freshItem->profit);
        $this->assertEquals(18000.00, $order->fresh()->calculateProfit());
    }

    public function test_filament_margin_column_computation_does_not_query_device_units(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'price_at_purchase' => 50000.00,
            'cost_price_at_purchase' => 35000.00,
            'cost_total_at_purchase' => 70000.00,
        ]);

        // Clear loaded relations to simulate fresh query
        $order = Order::with('items')->find($order->id);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $profit = $order->calculateProfit();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // 50,000 * 2 = 100,000 - 70,000 = 30,000
        $this->assertEquals(30000.00, $profit);

        // Ensure no query to device_units was made
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('device_units', $query['query']);
        }
    }
}
