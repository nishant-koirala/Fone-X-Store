<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductCondition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Display storefront homepage with eager-loaded featured listings & accessory categories.
     */
    public function index(): View
    {
        $featuredConditions = ProductCondition::with(['product', 'product.category'])
            ->whereHas('product', function ($q) {
                $q->where('is_visible', true);
            })
            ->where('quantity_in_stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        $accessoryCategories = Cache::remember('home_accessory_categories', 3600, function () {
            return Category::whereIn('slug', ['chargers-cables', 'cases-covers', 'audio-speakers', 'power-banks', 'accessories'])->get();
        });

        $heroConditions = ProductCondition::with(['product', 'product.category'])
            ->where('quantity_in_stock', '>', 0)
            ->where('grade', 'new')
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('featuredConditions', 'accessoryCategories', 'heroConditions'));
    }
}
