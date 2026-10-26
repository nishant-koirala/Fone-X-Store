<?php

namespace App\Http\Controllers;

use App\Models\ProductCondition;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart page with line items, subtotal, and total.
     */
    public function index(CartService $cartService): View
    {
        $cartItems = $cartService->getItems();
        $subtotal = $cartService->subtotal();
        $total = $cartService->total();

        return view('cart.index', compact('cartItems', 'subtotal', 'total'));
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

        if (! $condition->product || ! $condition->product->is_visible) {
            return back()->with('error', 'This product is currently unavailable.');
        }

        $requestedQty = (int) $request->input('quantity', 1);
        $productName = $condition->product->name;
        $gradeStr = $condition->grade->value;

        if ($condition->quantity_in_stock < $requestedQty) {
            return back()->with('error', "Insufficient stock for {$productName} (Grade ".strtoupper($gradeStr)."). Only {$condition->quantity_in_stock} items remaining.");
        }

        $cart = session()->get('cart', []);
        $condId = $condition->id;
        $currentQty = isset($cart[$condId]) ? (int) $cart[$condId]['quantity'] : 0;
        $newQty = $currentQty + $requestedQty;

        if ($newQty > $condition->quantity_in_stock) {
            return back()->with('error', "Cannot add more. Stock limit for {$productName} (Grade ".strtoupper($gradeStr).") is {$condition->quantity_in_stock}.");
        }

        $cart[$condId] = [
            'product_condition_id' => $condId,
            'quantity' => $newQty,
        ];

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', "{$productName} (Grade ".strtoupper($gradeStr).') added to your cart.');
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

        if (! $condition->product || ! $condition->product->is_visible) {
            return back()->with('error', 'This product is currently unavailable.');
        }

        $requestedQty = (int) $request->input('quantity');
        $productName = $condition->product->name;
        $gradeStr = $condition->grade->value;

        if ($requestedQty > $condition->quantity_in_stock) {
            return back()->with('error', "Only {$condition->quantity_in_stock} items available for {$productName} (Grade ".strtoupper($gradeStr).').');
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
