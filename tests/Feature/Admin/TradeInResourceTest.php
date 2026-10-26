<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\TradeInValuationResource\Pages\EditTradeInValuation;
use App\Filament\Resources\TradeInValuationResource\Pages\ListTradeInValuations;
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
        $this->assertEquals('reviewed', $fresh->status);
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

        $this->assertEquals('completed', $valuation->fresh()->status);
    }
}
