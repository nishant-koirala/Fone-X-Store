<?php

namespace App\Actions\Checkout;

use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use App\Rules\NepaliPhone;
use App\Services\Inventory\UnitInventoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PlaceOrder
{
    /**
     * Process checkout submission inside a database transaction, lock stock, create order, and deduct inventory.
     *
     * @param  array{name: string, phone: string, email?: ?string, address: string, delivery_zone?: string}  $data
     * @param  array<int|string, array{quantity: int|string}>|null  $cartSession
     *
     * @throws InsufficientStockException
     */
    public function execute(array $data, ?array $cartSession = null, ?int $userId = null): Order
    {
        $cart = $cartSession ?? session('cart', []);

        if (empty($cart)) {
            throw new InsufficientStockException('Your cart is empty.');
        }

        return DB::transaction(function () use ($data, $cart, $userId): Order {
            $conditionIds = array_keys($cart);

            /** @var Collection<int, ProductCondition> $conditions */
            $conditions = ProductCondition::with('product')
                ->whereIn('id', $conditionIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $orderItemsData = [];
            $orderTotal = 0;

            // 1. Re-verify stock & visibility for every line item
            foreach ($cart as $condId => $item) {
                if (! isset($conditions[$condId])) {
                    throw new InsufficientStockException('One of the selected items is no longer available.');
                }

                $condition = $conditions[$condId];

                if (! $condition->product || ! $condition->product->is_visible) {
                    throw new InsufficientStockException("The product '{$condition->product?->name}' is no longer available for purchase.");
                }

                $requestedQty = (int) ($item['quantity'] ?? 1);
                $prodName = $condition->product->name;
                $gradeStr = $condition->grade->value;

                if ($condition->quantity_in_stock < $requestedQty) {
                    throw new InsufficientStockException("Stock for {$prodName} (Grade ".strtoupper($gradeStr).") is insufficient. Only {$condition->quantity_in_stock} items remaining in stock.");
                }

                $price = (float) $condition->price;
                $lineTotal = $price * $requestedQty;
                $orderTotal += $lineTotal;

                $orderItemsData[] = [
                    'condition' => $condition,
                    'quantity' => $requestedQty,
                    'price_at_purchase' => $price,
                ];
            }

            // 2. Normalize Nepali phone & match Customer by primary phone number without overwriting existing data
            $phone = NepaliPhone::normalize($data['phone']) ?? trim($data['phone']);
            $shippingName = trim($data['name']);
            $shippingEmail = ! empty($data['email']) ? trim((string) $data['email']) : null;
            $shippingAddress = trim($data['address']);
            $deliveryZone = $data['delivery_zone'] ?? 'inside_valley';

            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $shippingName,
                    'email' => $shippingEmail,
                    'address' => $shippingAddress,
                ]
            );

            // Update customer profile if address was empty or placeholder, or if email/name was missing
            $customerUpdates = [];
            if (empty($customer->address) || $customer->address === 'Trade-In Request Submission') {
                $customerUpdates['address'] = $shippingAddress;
            }
            if (empty($customer->email) && $shippingEmail) {
                $customerUpdates['email'] = $shippingEmail;
            }
            if (empty($customer->name) && $shippingName) {
                $customerUpdates['name'] = $shippingName;
            }
            if (! empty($customerUpdates)) {
                $customer->update($customerUpdates);
            }

            // 3. Create Order record with shipping snapshot and delivery zone
            /** @var Order $order */
            $order = Order::create([
                'customer_id' => $customer->id,
                'user_id' => $userId,
                'status' => OrderStatusEnum::PENDING,
                'delivery_zone' => $deliveryZone,
                'total' => $orderTotal,
                'shipping_name' => $shippingName,
                'shipping_phone' => $phone,
                'shipping_email' => $shippingEmail,
                'shipping_address' => $shippingAddress,
            ]);

            // 4. Create OrderItems, assign units for tracked conditions, and deduct stock
            $unitService = app(UnitInventoryService::class);

            foreach ($orderItemsData as $itemData) {
                /** @var ProductCondition $condition */
                $condition = $itemData['condition'];
                $qty = (int) $itemData['quantity'];

                $unitCost = $condition->cost_price !== null ? (float) $condition->cost_price : null;
                $totalCost = $unitCost !== null ? round($unitCost * $qty, 2) : null;

                /** @var OrderItem $orderItem */
                $orderItem = $order->items()->create([
                    'product_condition_id' => $condition->id,
                    'quantity' => $qty,
                    'price_at_purchase' => $itemData['price_at_purchase'],
                    'cost_price_at_purchase' => $unitCost,
                    'cost_total_at_purchase' => $totalCost,
                ]);

                // Auto-assign available serialized units if this condition tracks units
                $assignedUnits = $unitService->assignUnitsToOrderItem($orderItem, null, $userId);

                // Compute cost price from units if tracked
                if ($condition->tracks_units && $assignedUnits->isNotEmpty()) {
                    $sumCost = 0.0;
                    $hasNullCost = false;
                    foreach ($assignedUnits as $unit) {
                        $uCost = $unit->cost_price !== null
                            ? (float) $unit->cost_price
                            : ($condition->cost_price !== null ? (float) $condition->cost_price : null);

                        if ($uCost === null) {
                            $hasNullCost = true;
                            break;
                        }
                        $sumCost += $uCost;
                    }

                    $orderItem->update([
                        'cost_price_at_purchase' => $hasNullCost ? null : round($sumCost / $qty, 2),
                        'cost_total_at_purchase' => $hasNullCost ? null : round($sumCost, 2),
                    ]);
                }

                // Call stock movement helper to deduct stock (type: sale)
                $condition->recordStockMovement(
                    type: StockMovementTypeEnum::SALE,
                    quantity: -$qty,
                    note: "Customer sale for Order #{$order->id}",
                    createdBy: $userId
                );
            }

            return $order;
        });
    }
}
