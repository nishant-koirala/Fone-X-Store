<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_staff_created_order_sets_confirmed_at_automatically(): void
    {
        $customer = Customer::factory()->create();
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5, 'price' => 50000]);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING->value,
                'delivery_zone' => 'inside_valley',
                'items' => [
                    [
                        'product_condition_id' => $condition->id,
                        'quantity' => 1,
                        'price_at_purchase' => 50000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->confirmed_at);
        $this->assertTrue($order->confirmed_at->isToday());
    }

    public function test_cancelled_order_cannot_change_to_another_status_on_model(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::CANCELLED,
            'restocked_at' => now(),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('A cancelled order cannot be changed to another status');

        $order->update(['status' => OrderStatusEnum::PAID]);
    }

    public function test_cancelled_order_can_save_without_changing_status(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::CANCELLED,
            'restocked_at' => now(),
        ]);

        // Updating notes or other non-status fields should succeed
        $order->update(['shipping_address' => 'Updated Kathmandu Address']);
        $this->assertSame('Updated Kathmandu Address', $order->fresh()->shipping_address);
    }

    public function test_filament_order_edit_restricts_status_for_cancelled_orders(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::CANCELLED,
            'restocked_at' => now(),
        ]);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->assertFormFieldIsDisabled('status');
    }
}
