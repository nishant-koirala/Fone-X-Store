<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\TradeInValuation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeInValuationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valuation_belongs_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $valuation = TradeInValuation::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $valuation->customer);
        $this->assertEquals($customer->id, $valuation->customer->id);
    }

    public function test_valuation_casts_estimated_value(): void
    {
        $valuation = TradeInValuation::factory()->create(['estimated_value' => '42000.75']);
        $this->assertSame('42000.75', (string) $valuation->estimated_value);
    }

    public function test_valuation_statuses(): void
    {
        $pending = TradeInValuation::factory()->pending()->create();
        $this->assertSame('pending', $pending->status);

        $reviewed = TradeInValuation::factory()->reviewed(50000)->create();
        $this->assertSame('reviewed', $reviewed->status);
        $this->assertSame('50000.00', (string) $reviewed->estimated_value);

        $completed = TradeInValuation::factory()->completed(50000)->create();
        $this->assertSame('completed', $completed->status);
    }
}
