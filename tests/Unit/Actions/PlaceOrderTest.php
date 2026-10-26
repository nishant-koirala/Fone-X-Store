<?php

namespace Tests\Unit\Actions;

use App\Actions\Checkout\PlaceOrder;
use App\Enums\GradeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    protected PlaceOrder $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new PlaceOrder;
    }

    public function test_places_order_successfully_inside_transaction(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 15', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => GradeEnum::NEW,
            'price' => 150000,
            'quantity_in_stock' => 10,
        ]);

        $orderData = [
            'name' => 'Suman Adhikari',
            'phone' => '9841234567',
            'email' => 'suman@example.com',
            'address' => 'New Baneshwor, Kathmandu',
        ];

        $cartSession = [
            $condition->id => ['quantity' => 2],
        ];

        $order = $this->action->execute($orderData, $cartSession);

        $this->assertNotNull($order->id);
        $this->assertSame(OrderStatusEnum::PENDING, $order->status);
        $this->assertSame('300000.00', (string) $order->total);
        $this->assertSame('Suman Adhikari', $order->shipping_name);
        $this->assertSame('9841234567', $order->shipping_phone);
        $this->assertSame('suman@example.com', $order->shipping_email);
        $this->assertSame('New Baneshwor, Kathmandu', $order->shipping_address);

        // Check stock deducted correctly: 10 - 2 = 8
        $this->assertSame(8, $condition->fresh()->quantity_in_stock);

        // Check stock movement created with type 'sale'
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => StockMovementTypeEnum::SALE->value,
            'quantity' => -2,
        ]);

        // Check customer created with phone
        $this->assertDatabaseHas('customers', [
            'phone' => '9841234567',
            'name' => 'Suman Adhikari',
        ]);
    }

    public function test_throws_exception_on_empty_cart(): void
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Your cart is empty.');

        $this->action->execute([
            'name' => 'Test User',
            'phone' => '9841000000',
            'address' => 'Kathmandu',
        ], []);
    }

    public function test_throws_exception_when_stock_insufficient(): void
    {
        $product = Product::factory()->create(['name' => 'Pixel 8', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => GradeEnum::A,
            'price' => 70000,
            'quantity_in_stock' => 1,
        ]);

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('insufficient');

        $this->action->execute([
            'name' => 'Test Customer',
            'phone' => '9841112233',
            'address' => 'Patan',
        ], [
            $condition->id => ['quantity' => 2],
        ]);
    }

    public function test_throws_exception_when_product_invisible(): void
    {
        $product = Product::factory()->create(['name' => 'Secret Phone', 'is_visible' => false]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 50000,
            'quantity_in_stock' => 5,
        ]);

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('no longer available');

        $this->action->execute([
            'name' => 'Customer',
            'phone' => '9841555555',
            'address' => 'Pokhara',
        ], [
            $condition->id => ['quantity' => 1],
        ]);
    }

    public function test_associates_verified_user_id(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 10000,
            'quantity_in_stock' => 5,
        ]);

        $order = $this->action->execute([
            'name' => 'Verified Member',
            'phone' => '9841999999',
            'address' => 'Biratnagar',
        ], [
            $condition->id => ['quantity' => 1],
        ], $user->id);

        $this->assertSame($user->id, $order->user_id);
    }
}
