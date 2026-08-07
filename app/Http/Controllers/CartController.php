<?php

namespace App\Http\Controllers;

use App\Models\ProductCondition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart page with line items and subtotal.
     */
    public function index(): View
    {
        $cartSession = session('cart', []);
        $cartItems = [];
        $subtotal = 0;

        if (!empty($cartSession)) {
            $conditionIds = array_keys($cartSession);
            $conditions = ProductCondition::with(['product', 'product.category'])
                ->whereIn('id', $conditionIds)
                ->get()
                ->keyBy('id');

            foreach ($cartSession as $condId => $item) {
                if (isset($conditions[$condId])) {
                    $condition = $conditions[$condId];
                    $qty = min($item['quantity'], $condition->quantity_in_stock);
                    
                    if ($qty > 0) {
                        $lineTotal = $condition->price * $qty;
                        $subtotal += $lineTotal;

                        $cartItems[] = [
                            'condition' => $condition,
                            'quantity' => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                }
            }
        }

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    /**
     * Add a product condition item to the session cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_condition_id' => 'required|exists:product_conditions,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $condition = ProductCondition::with('product')->findOrFail($request->input('product_condition_id'));
        $requestedQty = (int) $request->input('quantity', 1);

        if ($condition->quantity_in_stock < $requestedQty) {
            return back()->with('error', "Insufficient stock for {$condition->product->name} (Grade " . strtoupper($condition->grade) . "). Only {$condition->quantity_in_stock} items remaining.");
        }

        $cart = session()->get('cart', []);
        $condId = $condition->id;
        $currentQty = isset($cart[$condId]) ? $cart[$condId]['quantity'] : 0;
        $newQty = $currentQty + $requestedQty;

        if ($newQty > $condition->quantity_in_stock) {
            return back()->with('error', "Cannot add more. Stock limit for {$condition->product->name} (Grade " . strtoupper($condition->grade) . ") is {$condition->quantity_in_stock}.");
        }

        $cart[$condId] = [
            'product_condition_id' => $condId,
            'quantity' => $newQty,
        ];

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', "{$condition->product->name} (Grade " . strtoupper($condition->grade) . ") added to your cart.");
    }

    /**
     * Update quantity for a specific cart line item.
     */
    public function update(Request $request, int $conditionId): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $condition = ProductCondition::with('product')->findOrFail($conditionId);
        $requestedQty = (int) $request->input('quantity');

        if ($requestedQty > $condition->quantity_in_stock) {
            return back()->with('error', "Only {$condition->quantity_in_stock} items available for {$condition->product->name} (Grade " . strtoupper($condition->grade) . ").");
        }

        $cart = session()->get('cart', []);
        if (isset($cart[$conditionId])) {
            $cart[$conditionId]['quantity'] = $requestedQty;
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the session cart.
     */
    public function destroy(int $conditionId): RedirectResponse
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$conditionId])) {
            unset($cart[$conditionId]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }
}
