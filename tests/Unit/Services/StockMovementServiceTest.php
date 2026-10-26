<?php

namespace Tests\Unit\Services;

use App\Enums\StockMovementTypeEnum;
use App\Models\ProductCondition;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_restock_movement_and_increments_quantity(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);
        $user = User::factory()->create();

        $movement = $condition->recordStockMovement(
            type: 'restock',
            quantity: 10,
            note: 'Shipment received from distributor',
            createdBy: $user->id
        );

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertEquals(StockMovementTypeEnum::RESTOCK, $movement->type);
        $this->assertEquals(10, $movement->quantity);
        $this->assertEquals('Shipment received from distributor', $movement->note);
        $this->assertEquals($user->id, $movement->created_by);

        $this->assertEquals(15, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'product_condition_id' => $condition->id,
            'quantity' => 10,
        ]);
    }

    public function test_records_sale_movement_and_decrements_quantity(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 10]);

        $movement = $condition->recordStockMovement(
            type: 'sale',
            quantity: -2,
            note: 'Order #1001 sale'
        );

        $this->assertEquals(StockMovementTypeEnum::SALE, $movement->type);
        $this->assertEquals(-2, $movement->quantity);
        $this->assertEquals(8, $condition->fresh()->quantity_in_stock);
    }

    public function test_records_exchange_in_and_adjustment_movements(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 2]);

        $condition->recordStockMovement(type: 'exchange_in', quantity: 1, note: 'Trade-in accepted');
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);

        $condition->recordStockMovement(type: 'adjustment', quantity: -1, note: 'Damaged item removed');
        $this->assertEquals(2, $condition->fresh()->quantity_in_stock);
    }

    public function test_rolls_back_transaction_on_failure(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);

        try {
            DB::transaction(function () use ($condition) {
                $condition->recordStockMovement('restock', 10);
                throw new Exception('Simulated mid-operation transaction failure');
            });
        } catch (Exception $e) {
            // Expected simulation
        }

        // Verify that neither the stock nor the movement row persisted
        $this->assertEquals(5, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_condition_id' => $condition->id,
            'quantity' => 10,
        ]);
    }

    public function test_rejects_stock_going_negative(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 3]);

        $this->expectException(\InvalidArgumentException::class);
        $condition->recordStockMovement(type: 'sale', quantity: -5, note: 'Attempt oversell');
    }
}
