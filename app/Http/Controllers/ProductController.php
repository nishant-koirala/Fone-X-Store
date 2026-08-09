<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a paginated catalog of ProductCondition listings with server-side filters & caching optimizations.
     */
    public function index(Request $request): View
    {
        $query = ProductCondition::query()
            ->with([
                'product' => function ($q) {
                    $q->withCount(['reviews' => fn ($r) => $r->where('is_approved', true)])
                      ->withAvg(['reviews' => fn ($r) => $r->where('is_approved', true)], 'rating');
                },
                'product.category'
            ])
            ->whereHas('product', function ($q) {
                $q->where('is_visible', true);
            });

        // Filter by Category Slug
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            if ($catSlug === 'accessories') {
                $query->whereHas('product.category', function ($q) {
                    $q->whereIn('slug', ['accessories', 'chargers-cables', 'cases-covers', 'audio-speakers', 'power-banks']);
                });
            } else {
                $query->whereHas('product.category', function ($q) use ($catSlug) {
                    $q->where('slug', $catSlug);
                });
            }
        } else {
            // If no category is selected, exclude all accessories so they only show on their dedicated page
            $query->whereHas('product.category', function ($q) {
                $q->whereNotIn('slug', ['accessories', 'chargers-cables', 'cases-covers', 'audio-speakers', 'power-banks']);
            });
        }

        // Filter by Brand
        if ($request->filled('brand')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand', $request->input('brand'));
            });
        }

        // Filter by Search (Name or Brand)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by Condition Grade (new, used, A, B, C)
        if ($request->filled('grade')) {
            $grade = $request->input('grade');
            if ($grade === 'used') {
                $query->whereIn('grade', ['A', 'B', 'C']);
            } elseif (in_array($grade, ['new', 'A', 'B', 'C'])) {
                $query->where('grade', $grade);
            }
        }

        // Filter by Price Range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // Apply Sorting
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $conditions = $query->paginate(12)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $brands = Product::distinct()->orderBy('brand')->pluck('brand');

        return view('products.index', compact('conditions', 'categories', 'brands'));
    }

    /**
     * Display a single product's detail page with all available condition options.
     */
    public function show(Product $product): View
    {
        abort_if(! $product->is_visible, 404);

        $product->load([
            'conditions',
            'category',
            'reviews' => fn ($q) => $q->where('is_approved', true)->latest()
        ]);
        $product->loadAvg(['reviews' => fn ($q) => $q->where('is_approved', true)], 'rating');
        $product->loadCount(['reviews' => fn ($q) => $q->where('is_approved', true)]);

        $relatedProducts = Product::with(['conditions', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_visible', true)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Store a new product review.
     */
    public function storeReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $product->reviews()->create([
            'name' => $validated['name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => false,
        ]);

        return back()->with('success', 'Thank you! Your review has been submitted and is pending approval.');
    }
}
