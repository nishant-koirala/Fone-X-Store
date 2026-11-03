<?php

namespace Tests\Unit\Models;

use App\Actions\Checkout\PlaceOrder;
use App\Enums\DeviceUnitStatusEnum;
use App\Models\DeviceUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Rules\Imei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemProfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracked_item_with_multiple_units_calculates_profit_from_summed_cost(): void
    {
        $order = Order::factory()->create();
        $condition = ProductCondition::factory()->create([
            'price' => 50000.00,
            'tracks_units' => true,
        ]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 2,
            'price_at_purchase' => 50000.00,
            'cost_price_at_purchase' => 75000.00, // Sum of unit 1 (35,000) and unit 2 (40,000)
        ]);

        DeviceUnit::factory()->create([
            'order_item_id' => $item->id,
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::SOLD,
            'cost_price' => 35000.00,
        ]);

        DeviceUnit::factory()->create([
            'order_item_id' => $item->id,
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::SOLD,
            'cost_price' => 40000.00,
        ]);

        // Revenue: 50,000 * 2 = 100,000. Total Cost: 75,000. Gross Profit: 25,000.
        $this->assertEquals(25000.00, $item->profit);
        $this->assertEquals(25000.00, $order->calculateProfit());
    }

    public function test_untracked_item_calculates_profit_from_per_unit_cost(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'price_at_purchase' => 1000.00,
            'cost_price_at_purchase' => 600.00, // Per-unit cost
        ]);

        // Revenue: 1,000 * 3 = 3,000. Total Cost: 600 * 3 = 1,800. Profit: 1,200.
        $this->assertEquals(1200.00, $item->profit);
        $this->assertEquals(1200.00, $order->calculateProfit());
    }

    public function test_tracked_order_snapshots_cost_falling_back_to_condition_cost(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 40000.00,
            'cost_price' => 28000.00, // Fallback cost
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 25000.00, // Has specific cost
            'imei' => Imei::fake(),
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => null, // Falls back to condition cost 28,000
            'imei' => Imei::fake(),
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Bishal Sharma',
                'phone' => '9841234567',
                'address' => 'New Road, Kathmandu',
            ],
            cartSession: [
                $condition->id => ['quantity' => 2],
            ]
        );

        $item = $order->items->first();
        // Total cost = 25,000 + 28,000 = 53,000
        $this->assertEquals(53000.00, (float) $item->cost_price_at_purchase);
        // Revenue = 40,000 * 2 = 80,000. Profit = 80,000 - 53,000 = 27,000
        $this->assertEquals(27000.00, $item->profit);
        $this->assertEquals(27000.00, $order->calculateProfit());
    }

    public function test_tracked_order_with_unresolvable_cost_sets_cost_null_and_profit_null(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 30000.00,
            'cost_price' => null, // No condition cost
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => null, // No unit cost either
            'imei' => Imei::fake(),
        ]);

        $order = (new PlaceOrder)->execute(
            data: [
                'name' => 'Kiran Adhikari',
                'phone' => '9841234567',
                'address' => 'Patan, Lalitpur',
            ],
            cartSession: [
                $condition->id => ['quantity' => 1],
            ]
        );

        $item = $order->items->first();
        $this->assertNull($item->cost_price_at_purchase);
        $this->assertNull($item->profit);
        $this->assertNull($order->calculateProfit());
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
            'cost_price' => 36000.00,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $trackedCondition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'cost_price' => 38000.00,
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

        $order->load(['items.deviceUnits']);
        $trackedItem = $order->items->firstWhere('product_condition_id', $trackedCondition->id);
        $untrackedItem = $order->items->firstWhere('product_condition_id', $untrackedCondition->id);

        // Tracked item: Revenue 100,000, Total Cost 74,000 => Profit 26,000
        $this->assertEquals(74000.00, (float) $trackedItem->cost_price_at_purchase);
        $this->assertEquals(26000.00, $trackedItem->profit);

        // Untracked item: Revenue 3,000, Unit Cost 600 * 3 = 1,800 => Profit 1,200
        $this->assertEquals(600.00, (float) $untrackedItem->cost_price_at_purchase);
        $this->assertEquals(1200.00, $untrackedItem->profit);

        // Combined Order Profit: 26,000 + 1,200 = 27,200
        $this->assertEquals(27200.00, $order->calculateProfit());
    }
}
