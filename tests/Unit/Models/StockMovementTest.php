<?php

namespace Tests\Unit\Models;

use App\Models\ProductCondition;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_movement_belongs_to_product_condition(): void
    {
        $condition = ProductCondition::factory()->create();
        $movement = StockMovement::factory()->create(['product_condition_id' => $condition->id]);

        $this->assertInstanceOf(ProductCondition::class, $movement->productCondition);
        $this->assertEquals($condition->id, $movement->productCondition->id);
    }

    public function test_stock_movement_belongs_to_creator(): void
    {
        $user = User::factory()->create();
        $movement = StockMovement::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $movement->creator);
        $this->assertEquals($user->id, $movement->creator->id);
    }

    public function test_stock_movement_casts_quantity(): void
    {
        $movement = StockMovement::factory()->create(['quantity' => '5']);
        $this->assertSame(5, $movement->quantity);
    }
}
