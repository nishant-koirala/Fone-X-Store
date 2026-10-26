<?php

namespace Tests\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\User;

trait InteractsWithShop
{
    /**
     * Create a product with realistic conditions and stock.
     *
     * @param  array|null  $conditions  Custom array of condition definitions: [['grade' => 'new', 'price' => 120000, 'stock' => 5], ...]
     */
    public function createProductWithConditions(array $productAttributes = [], ?array $conditions = null): Product
    {
        if (! isset($productAttributes['category_id'])) {
            $productAttributes['category_id'] = Category::factory()->create()->id;
        }

        $product = Product::factory()->create($productAttributes);

        if ($conditions === null) {
            $conditions = [
                ['grade' => 'new', 'price' => $product->base_price, 'original_price' => $product->base_price * 1.1, 'quantity_in_stock' => 10],
                ['grade' => 'A', 'price' => round($product->base_price * 0.85, 2), 'original_price' => $product->base_price, 'quantity_in_stock' => 5],
                ['grade' => 'B', 'price' => round($product->base_price * 0.70, 2), 'original_price' => round($product->base_price * 0.85, 2), 'quantity_in_stock' => 3],
            ];
        }

        foreach ($conditions as $cond) {
            ProductCondition::create([
                'product_id' => $product->id,
                'grade' => $cond['grade'] ?? 'new',
                'price' => $cond['price'] ?? $product->base_price,
                'original_price' => $cond['original_price'] ?? null,
                'quantity_in_stock' => $cond['quantity_in_stock'] ?? ($cond['stock'] ?? 10),
            ]);
        }

        return $product->fresh(['conditions', 'category']);
    }

    /**
     * Create a single product condition with an attached product.
     */
    public function createCondition(array $attributes = []): ProductCondition
    {
        return ProductCondition::factory()->create($attributes);
    }

    /**
     * Authenticate as an admin user.
     */
    public function loginAsAdmin(?User $user = null): User
    {
        if ($user === null) {
            $user = User::factory()->admin()->create([
                'name' => 'Admin User',
                'email' => 'admin@fonexstore.test',
            ]);
        } else {
            $user->is_admin = true;
            $user->save();
        }

        $this->actingAs($user);

        return $user;
    }

    /**
     * Authenticate as a normal customer user.
     */
    public function loginAsCustomer(?User $user = null): User
    {
        $user ??= User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@fonexstore.test',
        ]);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Set up the session cart with line items.
     *
     * @param  array  $items  [ [condition_id => ['quantity' => 1]] or [condition => $cond, 'quantity' => 2] ]
     */
    public function setCartSession(array $items): static
    {
        $cart = [];
        foreach ($items as $key => $item) {
            if ($item instanceof ProductCondition) {
                $cart[$item->id] = [
                    'product_condition_id' => $item->id,
                    'quantity' => 1,
                ];
            } elseif (is_array($item) && isset($item['condition'])) {
                $cart[$item['condition']->id] = [
                    'product_condition_id' => $item['condition']->id,
                    'quantity' => $item['quantity'] ?? 1,
                ];
            } elseif (is_array($item) && isset($item['quantity'])) {
                $cart[$key] = [
                    'product_condition_id' => $key,
                    'quantity' => $item['quantity'],
                ];
            } else {
                $cart[$key] = $item;
            }
        }

        return $this->withSession(['cart' => $cart]);
    }
}
