<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductCondition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page with order summary.
     */
    public function index(): View|RedirectResponse
    {
        $cartSession = session('cart', []);
        if (empty($cartSession)) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Add items before checking out.');
        }

        $conditionIds = array_keys($cartSession);
        $conditions = ProductCondition::with(['product', 'product.category'])
            ->whereIn('id', $conditionIds)
            ->get()
            ->keyBy('id');

        $cartItems = [];
        $total = 0;

        foreach ($cartSession as $condId => $item) {
            if (isset($conditions[$condId])) {
                $condition = $conditions[$condId];
                $qty = min($item['quantity'], $condition->quantity_in_stock);
                if ($qty > 0) {
                    $lineTotal = $condition->price * $qty;
                    $total += $lineTotal;

                    $cartItems[] = [
                        'condition' => $condition,
                        'quantity' => $qty,
                        'line_total' => $lineTotal,
                    ];
                }
            }
        }

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('warning', 'Selected items in your cart are currently out of stock.');
        }

        return view('checkout.index', compact('cartItems', 'total'));
    }

    /**
     * Process checkout submission inside a database transaction and deduct stock via StockMovement.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:1000',
        ]);

        $cartSession = session('cart', []);
        if (empty($cartSession)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($request, $cartSession) {
                $conditionIds = array_keys($cartSession);
                $conditions = ProductCondition::with('product')
                    ->whereIn('id', $conditionIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $orderItemsData = [];
                $orderTotal = 0;

                // 1. Re-verify stock for every line item
                foreach ($cartSession as $condId => $item) {
                    if (!isset($conditions[$condId])) {
                        throw new Exception("One of the selected items is no longer available.");
                    }

                    $condition = $conditions[$condId];
                    $requestedQty = (int) $item['quantity'];

                    if ($condition->quantity_in_stock < $requestedQty) {
                        throw new Exception("Stock for {$condition->product->name} (Grade " . strtoupper($condition->grade) . ") is insufficient. Only {$condition->quantity_in_stock} items remaining in stock.");
                    }

                    $lineTotal = $condition->price * $requestedQty;
                    $orderTotal += $lineTotal;

                    $orderItemsData[] = [
                        'condition' => $condition,
                        'quantity' => $requestedQty,
                        'price_at_purchase' => $condition->price,
                    ];
                }

                // 2. Create or match Customer by primary phone contact number
                $customer = Customer::firstOrCreate(
                    ['phone' => trim($request->input('phone'))],
                    [
                        'name' => trim($request->input('name')),
                        'email' => $request->input('email') ? trim($request->input('email')) : null,
                        'address' => trim($request->input('address')),
                    ]
                );

                $customer->update([
                    'name' => trim($request->input('name')),
                    'email' => $request->input('email') ? trim($request->input('email')) : $customer->email,
                    'address' => trim($request->input('address')),
                ]);

                // 3. Create Order record with pending status (Cash on Delivery)
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'total' => $orderTotal,
                ]);

                // 4. Create OrderItems & deduct stock via transactional stock movement helper
                foreach ($orderItemsData as $itemData) {
                    /** @var ProductCondition $condition */
                    $condition = $itemData['condition'];
                    $qty = $itemData['quantity'];

                    $order->items()->create([
                        'product_condition_id' => $condition->id,
                        'quantity' => $qty,
                        'price_at_purchase' => $itemData['price_at_purchase'],
                    ]);

                    // Call stock movement helper to deduct stock (type: sale)
                    $condition->recordStockMovement(
                        type: 'sale',
                        quantity: -$qty,
                        note: "Customer sale for Order #{$order->id}",
                        createdBy: auth()->id()
                    );
                }

                return $order;
            });

            // Clear session cart and store order security verification ID
            session()->forget('cart');
            session(['last_order_id' => $order->id]);

            return redirect()->route('orders.confirmation', $order)->with('success', 'Order placed successfully!');

        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display order confirmation page for the authorized session order.
     */
    public function confirmation(Order $order): View|RedirectResponse
    {
        // Session security check: ensure order belongs to current checkout session
        if (session('last_order_id') != $order->id) {
            return redirect()->route('home')->with('error', 'Unauthorized access to order confirmation page.');
        }

        $order->load(['customer', 'items.productCondition.product', 'items.productCondition.product.category']);

        return view('orders.confirmation', compact('order'));
    }
}
