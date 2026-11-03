<?php

namespace Tests\Feature\Admin;

use App\Enums\TradeInStatusEnum;
use App\Filament\Resources\TradeInValuationResource\Pages\EditTradeInValuation;
use App\Filament\Resources\TradeInValuationResource\Pages\ListTradeInValuations;
use App\Models\ProductCondition;
use App\Models\TradeInValuation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TradeInResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_list_trade_in_valuations_in_admin(): void
    {
        $valuations = TradeInValuation::factory()->count(2)->create();

        Livewire::test(ListTradeInValuations::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($valuations);
    }

    public function test_can_price_and_update_status_of_trade_in(): void
    {
        $valuation = TradeInValuation::factory()->pending()->create();

        Livewire::test(EditTradeInValuation::class, ['record' => $valuation->getRouteKey()])
            ->fillForm([
                'estimated_value' => 65000,
                'status' => 'reviewed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $valuation->fresh();
        $this->assertEquals(65000, (float) $fresh->estimated_value);
        $this->assertEquals(TradeInStatusEnum::REVIEWED, $fresh->status);
    }

    public function test_can_mark_trade_in_as_completed(): void
    {
        $valuation = TradeInValuation::factory()->reviewed(65000)->create();

        Livewire::test(EditTradeInValuation::class, ['record' => $valuation->getRouteKey()])
            ->fillForm([
                'status' => 'completed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(TradeInStatusEnum::COMPLETED, $valuation->fresh()->status);
    }

    public function test_trade_in_acceptance_ingests_stock_into_product_condition(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 2,
            'cost_price' => null,
        ]);

        $valuation = TradeInValuation::factory()->reviewed(45000)->create();

        // Ingest into inventory
        $movement = $valuation->ingestIntoStock($condition);

        $this->assertNotNull($movement);
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);
        $this->assertEquals('exchange_in', $movement->type->value);
        $this->assertEquals(1, $movement->quantity);
        $this->assertEquals(45000.0, (float) $condition->fresh()->cost_price);

        $freshVal = $valuation->fresh();
        $this->assertEquals(TradeInStatusEnum::COMPLETED, $freshVal->status);
        $this->assertEquals($condition->id, $freshVal->product_condition_id);
        $this->assertNotNull($freshVal->stock_ingested_at);
    }

    public function test_trade_in_stock_ingestion_is_strictly_idempotent(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);
        $valuation = TradeInValuation::factory()->reviewed(30000)->create();

        // First ingestion
        $valuation->ingestIntoStock($condition);
        $this->assertEquals(6, $condition->fresh()->quantity_in_stock);

        // Second ingestion must be a no-op
        $secondResult = $valuation->fresh()->ingestIntoStock($condition);
        $this->assertNull($secondResult);
        $this->assertEquals(6, $condition->fresh()->quantity_in_stock);
    }

    public function test_can_ingest_trade_in_via_filament_edit_save(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 1]);
        $valuation = TradeInValuation::factory()->reviewed(50000)->create();

        Livewire::test(EditTradeInValuation::class, ['record' => $valuation->getRouteKey()])
            ->fillForm([
                'status' => 'completed',
                'product_condition_id' => $condition->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(2, $condition->fresh()->quantity_in_stock);
        $this->assertNotNull($valuation->fresh()->stock_ingested_at);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'exchange_in',
            'quantity' => 1,
        ]);
    }
}
