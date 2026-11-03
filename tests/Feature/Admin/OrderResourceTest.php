<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
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

    public function test_deleting_non_cancelled_order_restocks_inventory(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition(['quantity_in_stock' => 5, 'price' => 30000]);

        // Create a pending order via Filament
        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 2,
                        'price_at_purchase' => 30000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock); // 5 - 2 = 3

        // Soft-delete the pending order; the deleting hook must restock
        $order->delete();

        $this->assertEquals(5, $condition->fresh()->quantity_in_stock); // restored
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 2,
            'note' => "Restock from deleted Order #{$order->id}",
        ]);
    }

    public function test_deleting_cancelled_order_does_not_double_restock(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition(['quantity_in_stock' => 5, 'price' => 30000]);

        // Create a pending order
        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 1,
                        'price_at_purchase' => 30000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $this->assertEquals(4, $condition->fresh()->quantity_in_stock);

        // Cancel first (triggers restock via updated hook)
        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => OrderStatusEnum::CANCELLED->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(5, $condition->fresh()->quantity_in_stock);
        $this->assertNotNull($order->fresh()->restocked_at);

        // Now soft-delete the already-cancelled, already-restocked order
        $order->fresh()->delete();

        // Stock must NOT increase again (deleting hook skipped because restocked_at is set)
        $this->assertEquals(5, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_condition_id' => $condition->id,
            'note' => "Restock from deleted Order #{$order->id}",
        ]);
    }

    public function test_concurrent_creation_with_insufficient_stock_fails_atomically(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition(['quantity_in_stock' => 1, 'price' => 50000]);

        // Simulate the race: deplete the unit from outside the Filament form
        // (e.g., a storefront order just beat this staff order to the transaction)
        $condition->update(['quantity_in_stock' => 0]);

        $initialOrderCount = Order::count();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 1,
                        'price_at_purchase' => 50000,
                    ],
                ],
            ])
            ->call('create');

        // No order should have been created
        $this->assertEquals($initialOrderCount, Order::count());
        // Stock must remain at 0 — no phantom restock
        $this->assertEquals(0, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_condition_id' => $condition->id,
        ]);
    }

    public function test_admin_order_creation_snapshots_cost_price_and_computes_margin(): void
    {
        $customer = Customer::factory()->create();
        $condition = $this->createCondition([
            'quantity_in_stock' => 5,
            'price' => 60000,
            'cost_price' => 45000,
        ]);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 2,
                        'price_at_purchase' => 60000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $item = $order->items->first();

        $this->assertEquals(45000.0, (float) $item->cost_price_at_purchase);
        // (60000 - 45000) * 2 = 30000
        $this->assertEquals(30000.0, $order->calculateProfit());
    }

    public function test_can_confirm_order_by_phone_in_admin_table(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING,
            'confirmed_at' => null,
        ]);

        Livewire::test(ListOrders::class)
            ->callTableAction('confirmPhone', $order)
            ->assertHasNoTableActionErrors();

        $this->assertNotNull($order->fresh()->confirmed_at);
    }
}
