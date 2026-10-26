<?php

namespace Tests\Unit\Models;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_condition_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $condition->product);
        $this->assertEquals($product->id, $condition->product->id);
    }

    public function test_condition_has_many_stock_movements(): void
    {
        $condition = ProductCondition::factory()->create();
        $movement = StockMovement::factory()->create(['product_condition_id' => $condition->id]);

        $this->assertTrue($condition->stockMovements->contains($movement));
    }

    public function test_condition_has_many_order_items(): void
    {
        $condition = ProductCondition::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_condition_id' => $condition->id]);

        $this->assertTrue($condition->orderItems->contains($orderItem));
    }

    public function test_condition_casts_attributes(): void
    {
        $condition = ProductCondition::factory()->create([
            'price' => '45000.00',
            'original_price' => '50000.00',
            'quantity_in_stock' => '12',
        ]);

        $this->assertSame('45000.00', (string) $condition->price);
        $this->assertSame('50000.00', (string) $condition->original_price);
        $this->assertSame(12, $condition->quantity_in_stock);
    }
}
