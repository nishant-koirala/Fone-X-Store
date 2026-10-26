<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\StockMovementResource\Pages\CreateStockMovement;
use App\Filament\Resources\StockMovementResource\Pages\ListStockMovements;
use App\Models\ProductCondition;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockMovementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_list_stock_movements_in_admin(): void
    {
        $condition = ProductCondition::factory()->create();
        $movement = StockMovement::factory()->create(['product_condition_id' => $condition->id]);

        Livewire::test(ListStockMovements::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$movement]);
    }

    public function test_manual_stock_entry_in_admin_creates_movement_and_adjusts_stock(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 5,
        ]);

        Livewire::test(CreateStockMovement::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'type' => 'restock',
                'quantity' => 10,
                'note' => 'Warehouse intake consignment #405',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Condition stock adjusted: 5 + 10 = 15
        $this->assertEquals(15, $condition->fresh()->quantity_in_stock);

        // Movement row recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 10,
            'note' => 'Warehouse intake consignment #405',
        ]);
    }

    public function test_manual_negative_stock_adjustment_in_admin_decrements_stock(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 10,
        ]);

        Livewire::test(CreateStockMovement::class)
            ->fillForm([
                'product_condition_id' => $condition->id,
                'type' => 'adjustment',
                'quantity' => -3,
                'note' => 'Broken screen in showroom',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertEquals(7, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'quantity' => -3,
        ]);
    }
}
