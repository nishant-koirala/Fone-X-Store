<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
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

        $this->assertEquals('fulfilled', $order->fresh()->status);
    }
}
