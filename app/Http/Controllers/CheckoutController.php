<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\PlaceOrder;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page with order summary.
     */
    public function index(CartService $cartService): View|RedirectResponse
    {
        $cartSession = session('cart', []);
        if (empty($cartSession)) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Add items before checking out.');
        }

        if ($cartService->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Selected items in your cart are currently out of stock.');
        }

        $cartItems = $cartService->getItems();
        $total = $cartService->total();

        return view('checkout.index', compact('cartItems', 'total'));
    }

    /**
     * Process checkout submission inside a database transaction via PlaceOrder action.
     */
    public function store(CheckoutRequest $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $cartSession = session('cart', []);
        if (empty($cartSession)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            // Link user_id only if authenticated user is email-verified
            $verifiedUserId = (auth()->check() && auth()->user()?->hasVerifiedEmail()) ? auth()->id() : null;

            $order = $placeOrder->execute(
                data: $request->validated(),
                cartSession: $cartSession,
                userId: $verifiedUserId
            );

            // Clear session cart and store order security verification ID
            session()->forget('cart');
            session(['last_order_id' => $order->id]);

            // Dispatch OrderPlaced mail to the order's shipping_email snapshot wrapped in try-catch
            if ($order->shipping_email) {
                try {
                    Mail::to($order->shipping_email)->send(new OrderPlaced($order));
                } catch (\Throwable $mailException) {
                    Log::error("Failed sending OrderPlaced email for order #{$order->id}: {$mailException->getMessage()}", [
                        'exception' => $mailException,
                    ]);
                }
            }

            return redirect()->route('orders.confirmation', $order)->with('success', 'Order placed successfully!');

        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Order checkout failed: '.$e->getMessage(), [
                'exception' => $e,
                'payload' => $request->except(['_token']),
            ]);

            return back()->withInput()->with('error', 'An unexpected error occurred while processing your order. Please try again.');
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
