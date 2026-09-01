<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\TradeInValuation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard with their orders and trade-ins.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // We link guest checkouts to this user by matching their registered email address
        $customerIds = Customer::where('email', $user->email)->pluck('id');

        $orders = Order::with(['items.productCondition.product'])
            ->whereIn('customer_id', $customerIds)
            ->latest()
            ->get();

        $tradeIns = TradeInValuation::with('customer')
            ->whereIn('customer_id', $customerIds)
            ->latest()
            ->get();

        return view('dashboard', compact('orders', 'tradeIns'));
    }
}
