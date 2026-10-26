<?php

namespace App\Http\Controllers;

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

        // Orders and trade-ins are linked strictly by verified user account ID
        $orders = Order::with(['items.productCondition.product'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $tradeIns = TradeInValuation::with('customer')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('dashboard', compact('orders', 'tradeIns'));
    }
}
