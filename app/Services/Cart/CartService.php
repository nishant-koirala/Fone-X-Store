<?php

namespace App\Services\Cart;

use App\Models\ProductCondition;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * @var Collection<int, CartItemData>|null
     */
    protected ?Collection $cachedItems = null;

    /**
     * Retrieve all valid, visible, and in-stock items currently in the session cart.
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     * @return Collection<int, CartItemData>
     */
    public function getItems(?array $sessionCart = null): Collection
    {
        if ($sessionCart === null && $this->cachedItems !== null) {
            return $this->cachedItems;
        }

        $cart = $sessionCart ?? session('cart', []);

        if (empty($cart)) {
            $items = collect();
            if ($sessionCart === null) {
                $this->cachedItems = $items;
            }

            return $items;
        }

        $conditionIds = array_keys($cart);
        $conditions = ProductCondition::with(['product', 'product.category'])
            ->whereIn('id', $conditionIds)
            ->get()
            ->keyBy('id');

        $items = collect();

        foreach ($cart as $condId => $item) {
            if (! isset($conditions[$condId])) {
                continue;
            }

            $condition = $conditions[$condId];

            if (! $condition->product || ! $condition->product->is_visible) {
                continue;
            }

            $qty = min((int) ($item['quantity'] ?? 0), $condition->quantity_in_stock);

            if ($qty > 0) {
                $price = (float) $condition->price;
                $lineTotal = $price * $qty;

                $items->push(new CartItemData(
                    condition: $condition,
                    quantity: $qty,
                    price: $price,
                    lineTotal: $lineTotal
                ));
            }
        }

        if ($sessionCart === null) {
            $this->cachedItems = $items;
        }

        return $items;
    }

    /**
     * Calculate cart subtotal.
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     */
    public function subtotal(?array $sessionCart = null): float
    {
        return (float) $this->getItems($sessionCart)->sum('lineTotal');
    }

    /**
     * Calculate shipping cost (free for phone orders, flat for accessories-only, or configured).
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     */
    public function shipping(?array $sessionCart = null): float
    {
        $items = $this->getItems($sessionCart);

        if ($items->isEmpty()) {
            return 0.0;
        }

        $accessorySlugs = (array) config('shop.accessory_category_slugs', []);
        $hasPhoneOrOther = $items->contains(function (CartItemData $item) use ($accessorySlugs) {
            $categorySlug = $item->condition->product?->category?->slug;

            return ! in_array($categorySlug, $accessorySlugs, true);
        });

        // If order contains phone / non-accessory, shipping is free (0 NPR).
        if ($hasPhoneOrOther) {
            return 0.0;
        }

        // Accessories-only flat shipping (default 0 or config if set)
        return (float) config('shop.accessory_shipping_cost', 0.0);
    }

    /**
     * Calculate grand total (subtotal + shipping).
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     */
    public function total(?array $sessionCart = null): float
    {
        return $this->subtotal($sessionCart) + $this->shipping($sessionCart);
    }

    /**
     * Total item count across all line items.
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     */
    public function count(?array $sessionCart = null): int
    {
        return (int) $this->getItems($sessionCart)->sum('quantity');
    }

    /**
     * Determine if cart has no purchasable items.
     *
     * @param  array<int|string, array{quantity: int|string}>|null  $sessionCart
     */
    public function isEmpty(?array $sessionCart = null): bool
    {
        return $this->getItems($sessionCart)->isEmpty();
    }

    /**
     * Clear cached cart items.
     */
    public function clearCache(): void
    {
        $this->cachedItems = null;
    }
}
