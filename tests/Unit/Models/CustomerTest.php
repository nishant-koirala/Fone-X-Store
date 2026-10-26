<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Order;
use App\Models\TradeInValuation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_has_many_orders(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($customer->orders->contains($order));
    }

    public function test_customer_has_many_trade_in_valuations(): void
    {
        $customer = Customer::factory()->create();
        $valuation = TradeInValuation::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($customer->tradeInValuations->contains($valuation));
    }
}
