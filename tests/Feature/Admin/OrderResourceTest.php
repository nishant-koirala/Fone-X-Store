<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_list_orders_in_admin(): void
    {
        $orders = Order::factory()->count(2)->create();

        Livewire::test(ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($orders);
    }

    public function test_can_update_order_status_in_admin(): void
    {
        $order = Order::factory()->pending()->create();

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm([
                'status' => 'fulfilled',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(OrderStatusEnum::FULFILLED, $order->fresh()->status);
    }

    public function test_manual_order_creation_in_admin_deducts_stock_and_calculates_total(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition([
            'quantity_in_stock' => 10,
            'price' => 50000,
        ]);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 2,
                        'price_at_purchase' => 50000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(100000, (float) $order->total);
        $this->assertEquals(8, $condition->fresh()->quantity_in_stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'sale',
            'quantity' => -2,
            'note' => "Staff manual order #{$order->id}",
        ]);
    }

    public function test_manual_order_subsequent_cancellation_restocks_inventory(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition([
            'quantity_in_stock' => 10,
            'price' => 50000,
        ]);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 2,
                        'price_at_purchase' => 50000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $this->assertEquals(8, $condition->fresh()->quantity_in_stock);

        // Cancel order via EditOrder in Filament
        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm([
                'status' => OrderStatusEnum::CANCELLED->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(10, $condition->fresh()->quantity_in_stock);
        $this->assertNotNull($order->fresh()->restocked_at);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 2,
        ]);
    }

    public function test_manual_order_creation_rejects_quantity_exceeding_stock(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition([
            'quantity_in_stock' => 2,
            'price' => 50000,
        ]);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 5,
                        'price_at_purchase' => 50000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.quantity']);

        $this->assertEquals(2, $condition->fresh()->quantity_in_stock);
    }
}
