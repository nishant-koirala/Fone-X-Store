<?php

namespace App\Http\Controllers;

use App\Models\ProductCondition;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the public storefront homepage with recent product listings.
     */
    public function index(): View
    {
        $featuredConditions = ProductCondition::with(['product', 'product.category'])
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('featuredConditions'));
    }
}
