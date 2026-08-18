<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlistIds = $request->session()->get('wishlist', []);
        
        $wishlistProducts = Product::with(['conditions', 'category'])
            ->whereIn('id', $wishlistIds)
            ->where('is_visible', true)
            ->get();

        return view('wishlist.index', compact('wishlistProducts'));
    }

    public function toggle(Request $request, Product $product)
    {
        $wishlist = $request->session()->get('wishlist', []);
        
        if (in_array($product->id, $wishlist)) {
            // Remove
            $wishlist = array_diff($wishlist, [$product->id]);
            $message = 'Removed from wishlist.';
        } else {
            // Add
            $wishlist[] = $product->id;
            $message = 'Added to wishlist.';
        }
        
        // Save back to session (array_values re-indexes the array)
        $request->session()->put('wishlist', array_values($wishlist));

        return back()->with('success', $message);
    }
}
