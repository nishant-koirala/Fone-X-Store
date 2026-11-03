<?php

namespace Tests\Unit\Models;

use App\Enums\DeviceUnitStatusEnum;
use App\Models\DeviceUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use App\Models\TradeInValuation;
use App\Rules\Imei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_unit_belongs_to_product_condition(): void
    {
        $condition = ProductCondition::factory()->create();
        $unit = DeviceUnit::factory()->create(['product_condition_id' => $condition->id]);

        $this->assertInstanceOf(ProductCondition::class, $unit->productCondition);
        $this->assertEquals($condition->id, $unit->productCondition->id);
    }

    public function test_device_unit_can_be_marked_as_sold_to_order_item(): void
    {
        $unit = DeviceUnit::factory()->create(['status' => DeviceUnitStatusEnum::AVAILABLE]);
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $unit->markAsSold($item);

        $fresh = $unit->fresh();
        $this->assertEquals(DeviceUnitStatusEnum::SOLD, $fresh->status);
        $this->assertEquals($item->id, $fresh->order_item_id);
        $this->assertNotNull($fresh->sold_at);
    }

    public function test_device_unit_can_be_marked_as_returned(): void
    {
        $unit = DeviceUnit::factory()->sold()->create();

        $unit->markAsReturned('Customer reported battery drain');

        $fresh = $unit->fresh();
        $this->assertEquals(DeviceUnitStatusEnum::RETURNED, $fresh->status);
        $this->assertStringContainsString('battery drain', $fresh->notes);
    }

    public function test_device_unit_scopes(): void
    {
        DeviceUnit::factory()->available()->count(3)->create();
        DeviceUnit::factory()->sold()->count(2)->create();

        $this->assertEquals(3, DeviceUnit::available()->count());
        $this->assertEquals(2, DeviceUnit::sold()->count());
    }

    public function test_trade_in_ingestion_creates_linked_device_unit(): void
    {
        $condition = ProductCondition::factory()->create();
        $valuation = TradeInValuation::factory()->accepted(55000)->create([
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'condition_description' => 'Grade A with 92% battery',
        ]);

        $imei = Imei::fake();

        $valuation->ingestIntoStock(
            condition: $condition,
            staffUserId: null,
            imei: $imei,
            batteryHealth: 92
        );

        $unit = DeviceUnit::where('imei', $imei)->first();
        $this->assertNotNull($unit);
        $this->assertEquals($condition->id, $unit->product_condition_id);
        $this->assertEquals(92, $unit->battery_health);
        $this->assertEquals(55000.0, (float) $unit->cost_price);
        $this->assertEquals(DeviceUnitStatusEnum::AVAILABLE, $unit->status);
        $this->assertEquals($valuation->id, $unit->trade_in_valuation_id);
    }
}
