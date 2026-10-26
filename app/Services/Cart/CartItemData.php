<?php

namespace App\Services\Cart;

use App\Models\ProductCondition;
use ArrayAccess;

/**
 * Value object representing an evaluated cart line item.
 *
 * @implements ArrayAccess<string, mixed>
 */
class CartItemData implements ArrayAccess
{
    public function __construct(
        public ProductCondition $condition,
        public int $quantity,
        public float $price,
        public float $lineTotal,
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Value object is read-only
    }

    public function offsetUnset(mixed $offset): void
    {
        // Value object is read-only
    }

    /**
     * @return array{condition: ProductCondition, quantity: int, price: float, line_total: float}
     */
    public function toArray(): array
    {
        return [
            'condition' => $this->condition,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'line_total' => $this->lineTotal,
        ];
    }
}
