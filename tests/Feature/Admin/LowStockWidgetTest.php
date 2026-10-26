<?php

namespace Tests\Feature\Admin;

use App\Filament\Widgets\LowStockProducts;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LowStockWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_low_stock_widget_shows_products_below_threshold_and_excludes_others(): void
    {
        $criticallyLow = ProductCondition::factory()->create(['quantity_in_stock' => 1]);
        $outOfStock = ProductCondition::factory()->create(['quantity_in_stock' => 0]);
        $atThreshold = ProductCondition::factory()->create(['quantity_in_stock' => 4]);
        $abundantlyInStock = ProductCondition::factory()->create(['quantity_in_stock' => 5]);
        $highStock = ProductCondition::factory()->create(['quantity_in_stock' => 20]);

        Livewire::test(LowStockProducts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$criticallyLow, $outOfStock, $atThreshold])
            ->assertCanNotSeeTableRecords([$abundantlyInStock, $highStock]);
    }
}
