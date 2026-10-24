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

        // Fetch products marked as "Featured Phone" in the admin panel
        $heroConditions = ProductCondition::with(['product', 'product.category'])
            ->whereHas('product', function ($q) {
                $q->where('is_visible', true)
                  ->where('is_featured', true);
            })
            ->where('quantity_in_stock', '>', 0)
            ->latest()
            ->get();

        // Fallback gracefully to latest available products if none are marked as featured yet
        if ($heroConditions->isEmpty()) {
            $heroConditions = ProductCondition::with(['product', 'product.category'])
                ->whereHas('product', function ($q) {
                    $q->where('is_visible', true);
                })
                ->where('quantity_in_stock', '>', 0)
                ->latest()
                ->take(5)
                ->get();
        }

        // Ensure each phone appears once in the rotating slider
        $heroConditions = $heroConditions->unique('product_id')->values();

        return view('home', compact('featuredConditions', 'accessoryCategories', 'heroConditions'));
    }
}
