@extends('layouts.storefront')

@section('title', 'Phone Inventory & Catalog — FoneX Store')

@section('content')

    <!-- Catalog Page Header -->
    <div class="bg-brand-charcoal text-white border-b border-white/10 py-12 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 h-80 w-80 bg-brand-red/25 blur-[100px] pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">STORE CATALOG</span>
                    <h1 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-white mt-1">
                        Phone Inventory
                    </h1>
                </div>
                <div class="font-mono text-xs text-white/70">
                    SHOWING <span class="font-bold text-white">{{ $conditions->total() }}</span> AVAILABLE LISTINGS
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Catalog Main Container -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

        <!-- Glassmorphism Filter Bar -->
        <form method="GET" action="{{ route('products.index') }}" class="mb-10 glass-panel p-6 border border-gray-200 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                
                <!-- Category Filter -->
                <div>
                    <label for="filter-category" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">Category</label>
                    <select id="filter-category" name="category" class="w-full font-sans text-xs border border-gray-300 bg-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 shadow-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label for="filter-brand" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">Brand</label>
                    <select id="filter-brand" name="brand" class="w-full font-sans text-xs border border-gray-300 bg-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 shadow-sm">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ $brand }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Condition Grade Filter -->
                <div>
                    <label for="filter-grade" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">Condition</label>
                    <select id="filter-grade" name="grade" class="w-full font-sans text-xs border border-gray-300 bg-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 shadow-sm">
                        <option value="">All Conditions</option>
                        <option value="new" {{ request('grade') == 'new' ? 'selected' : '' }}>New Only</option>
                        <option value="used" {{ request('grade') == 'used' ? 'selected' : '' }}>All Pre-Owned (A/B/C)</option>
                        <option value="A" {{ request('grade') == 'A' ? 'selected' : '' }}>Grade A (Excellent)</option>
                        <option value="B" {{ request('grade') == 'B' ? 'selected' : '' }}>Grade B (Good)</option>
                        <option value="C" {{ request('grade') == 'C' ? 'selected' : '' }}>Grade C (Fair)</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div>
                    <label for="filter-sort" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">Sort By</label>
                    <select id="filter-sort" name="sort" class="w-full font-sans text-xs border border-gray-300 bg-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 shadow-sm">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Listed</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <button type="submit" class="w-full font-mono text-xs uppercase font-bold text-white bg-brand-red hover:bg-brand-red-dark py-2.5 transition-colors shadow-sm">
                        Filter Results
                    </button>
                    @if(request()->anyFilled(['category', 'brand', 'grade', 'sort', 'min_price', 'max_price']))
                        <a href="{{ route('products.index') }}" class="font-mono text-xs uppercase font-bold text-brand-charcoal border border-gray-300 bg-white hover:border-brand-red px-3 py-2.5 transition-colors whitespace-nowrap shadow-sm">
                            Reset
                        </a>
                    @endif
                </div>

            </div>
        </form>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($conditions as $condition)
                <div class="group card-glow-hover relative flex flex-col justify-between border border-gray-200 bg-white p-5">
                    
                    <!-- Condition & Stock Header -->
                    <div class="flex items-center justify-between mb-4">
                        @if(strtolower($condition->grade) === 'new')
                            <span class="border-2 border-brand-red text-brand-red font-mono text-[11px] font-bold uppercase tracking-wider px-2 py-0.5">
                                NEW
                            </span>
                        @else
                            <span class="bg-brand-red text-white font-mono text-[11px] font-bold uppercase tracking-wider px-2 py-0.5">
                                GRADE {{ strtoupper($condition->grade) }}
                            </span>
                        @endif

                        @if($condition->quantity_in_stock > 0)
                            <span class="font-mono text-[10px] text-emerald-700 font-semibold uppercase bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                                IN STOCK ({{ $condition->quantity_in_stock }})
                            </span>
                        @else
                            <span class="font-mono text-[10px] text-amber-700 font-semibold uppercase bg-amber-50 px-2 py-0.5 border border-amber-200">
                                OUT OF STOCK
                            </span>
                        @endif
                    </div>

                    <!-- Shine Container Placeholder -->
                    <div class="shine-container aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-6 relative overflow-hidden mb-5 border border-gray-100">
                        <svg class="h-28 w-28 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors duration-300 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                            <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                            <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                        </svg>

                        <span class="absolute bottom-2 left-2 font-mono text-[10px] uppercase tracking-wider text-brand-grey">
                            {{ strtoupper($condition->product->brand) }}
                        </span>

                        @if($condition->price < $condition->product->base_price)
                            @php $savings = round((($condition->product->base_price - $condition->price) / $condition->product->base_price) * 100); @endphp
                            <span class="absolute top-2 right-2 bg-brand-red text-white font-mono text-[10px] font-bold px-1.5 py-0.5 shadow">
                                SAVE {{ $savings }}%
                            </span>
                        @endif
                    </div>

                    <!-- Product Specs -->
                    <div class="space-y-1">
                        <div class="font-mono text-xs text-brand-grey uppercase tracking-wider">
                            {{ $condition->product->category->name ?? 'SMARTPHONE' }}
                        </div>
                        <a href="{{ route('products.show', $condition->product->slug) }}" class="font-sans font-bold text-brand-charcoal text-lg group-hover:text-brand-red transition-colors leading-snug block">
                            {{ $condition->product->name }}
                        </a>
                        <p class="font-mono text-xs text-brand-grey">
                            {{ $condition->product->brand }} • {{ strtoupper($condition->grade) }} CONDITION
                        </p>
                    </div>

                    <!-- Price & Action -->
                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-baseline justify-between">
                        <div>
                            <span class="font-mono text-xs text-brand-grey uppercase block">Price</span>
                            <div class="flex items-baseline space-x-2">
                                <span class="font-mono font-bold text-brand-charcoal text-xl">
                                    Rs {{ number_format($condition->price) }}
                                </span>
                                @if($condition->price < $condition->product->base_price)
                                    <span class="font-mono text-xs text-brand-grey line-through">
                                        Rs {{ number_format($condition->product->base_price) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('products.show', $condition->product->slug) }}" class="font-mono text-xs uppercase font-bold text-white bg-brand-charcoal group-hover:bg-brand-red px-3.5 py-2 transition-colors duration-150 shadow-sm">
                            View
                        </a>
                    </div>

                </div>
            @empty
                <!-- Empty State Message -->
                <div class="col-span-full border border-dashed border-gray-300 p-16 text-center bg-brand-offwhite">
                    <svg class="mx-auto h-12 w-12 text-brand-grey stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="mt-4 font-display text-xl uppercase tracking-tight text-brand-charcoal">No Matching Phone Conditions Found</h3>
                    <p class="mt-2 font-sans text-sm text-brand-grey max-w-md mx-auto">
                        No product listings match your selected category, brand, or condition filters. Try adjusting your filter choices or resetting them.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-2 font-mono text-xs uppercase font-bold text-white bg-brand-red hover:bg-brand-red-dark px-6 py-3 transition-colors shadow">
                            <span>Clear All Filters</span>
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($conditions->hasPages())
            <div class="mt-12">
                {{ $conditions->links() }}
            </div>
        @endif

    </div>

@endsection
