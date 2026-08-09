@extends('layouts.storefront')

@section('title', 'Phone Inventory & Catalog — FoneX Store')

@section('content')

    <!-- Catalog Page Header -->
    <div x-data="catalogFilter()" class="bg-brand-charcoal text-white border-b border-white/10 py-12 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 h-80 w-80 bg-brand-red/25 blur-[100px] pointer-events-none"></div>

        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">STORE CATALOG</span>
                    <h1 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-white mt-1">
                        Phone Inventory
                    </h1>
                </div>
                <div class="font-mono text-xs text-white/70">
                    SHOWING <span id="result-count" class="font-bold text-white">{{ $conditions->total() }}</span> AVAILABLE LISTINGS
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Catalog Main Container -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Left Sidebar (Sticky on Desktop) -->
            <div class="w-full lg:w-64 flex-shrink-0 lg:sticky lg:top-28 z-30">

                <!-- Mobile Filter Toggle Button -->
                <div class="lg:hidden mb-6">
                    <button type="button" @click="mobileFiltersOpen = true" class="w-full font-mono text-xs uppercase font-bold text-brand-charcoal bg-white border border-gray-300 py-3 shadow-sm hover:border-brand-red flex items-center justify-center space-x-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter & Sort Options</span>
                    </button>
                </div>

                <!-- Glassmorphism Filter Bar (Slide-over on Mobile) -->
                <form x-ref="filterForm" @submit.prevent="applyFilters" @change="applyFilters" method="GET" action="{{ route('products.index') }}" 
                      :class="mobileFiltersOpen ? 'fixed inset-0 z-[100] flex flex-col justify-end bg-brand-charcoal/50 backdrop-blur-sm' : 'hidden lg:block'"
                      class="lg:block">
            
            <!-- Mobile Overlay Click-to-Close -->
            <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="absolute inset-0 lg:hidden"></div>

            <div :class="mobileFiltersOpen ? 'relative bg-white w-full rounded-t-3xl p-6 shadow-2xl max-h-[85vh] overflow-y-auto transform transition-transform' : 'glass-panel p-6 border border-gray-200 shadow-sm'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                
                <!-- Mobile Header -->
                <div class="flex items-center justify-between mb-6 lg:hidden">
                    <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">Filters</h2>
                    <button type="button" @click="mobileFiltersOpen = false" class="p-2 text-brand-grey hover:text-brand-red">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col gap-5">
                
                <!-- Category Filter -->
                <div>
                    <label for="filter-category" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">Category</label>
                    <select id="filter-category" name="category" class="w-full font-sans text-xs border border-gray-300 bg-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 shadow-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            @php
                                $slug = is_object($cat) ? $cat->slug : ($cat['slug'] ?? $cat);
                                $name = is_object($cat) ? $cat->name : ($cat['name'] ?? $cat);
                            @endphp
                            <option value="{{ $slug }}" {{ request('category') == $slug ? 'selected' : '' }}>
                                {{ $name }}
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
                    <button type="submit" class="w-full font-mono text-xs uppercase font-bold text-white bg-brand-red hover:bg-brand-red-dark py-2.5 transition-all active:scale-95 shadow-sm">
                        Filter Results
                    </button>
                    @if(request()->anyFilled(['category', 'brand', 'grade', 'sort', 'min_price', 'max_price']))
                        <a href="{{ route('products.index') }}" @click.prevent="resetFilters" class="font-mono text-xs uppercase font-bold text-brand-charcoal border border-gray-300 bg-white hover:border-brand-red px-3 py-2.5 transition-all active:scale-95 whitespace-nowrap shadow-sm text-center">
                            Reset
                        </a>
                    @endif
                </div>

                <!-- Mobile Apply Button -->
                <div class="mt-6 lg:hidden">
                    <button type="button" @click="mobileFiltersOpen = false" class="w-full font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red py-3 shadow-sm transition-colors">
                        Show Results
                    </button>
                </div>

            </div>
            </div> <!-- End glass-panel -->
        </form>
        </div> <!-- End Left Sidebar -->

        <!-- Right Content Area -->
        <div class="flex-grow min-w-0">

        <!-- Active Filter Chips -->
        <div id="active-filters-container" class="mb-6 flex flex-wrap items-center gap-2 min-h-[28px]">
            @if(request()->anyFilled(['category', 'brand', 'grade', 'search']))
                <span class="font-mono text-[10px] uppercase text-brand-grey mr-2 tracking-wider">Active Filters:</span>
                
                @if(request('search'))
                    <span class="inline-flex items-center bg-brand-charcoal text-white font-mono text-[10px] uppercase px-2 py-1 space-x-1">
                        <span>Search: {{ request('search') }}</span>
                        <button type="button" @click="removeFilter('search')" class="hover:text-brand-red ml-1">×</button>
                    </span>
                @endif

                @if(request('category'))
                    @php 
                        $cat = collect($categories)->first(function($c) {
                            return (is_object($c) ? $c->slug : $c['slug']) === request('category');
                        });
                        $catName = $cat ? (is_object($cat) ? $cat->name : $cat['name']) : request('category');
                    @endphp
                    <span class="inline-flex items-center bg-brand-charcoal text-white font-mono text-[10px] uppercase px-2 py-1 space-x-1">
                        <span>Category: {{ $catName }}</span>
                        <button type="button" @click="removeFilter('category')" class="hover:text-brand-red ml-1">×</button>
                    </span>
                @endif

                @if(request('brand'))
                    <span class="inline-flex items-center bg-brand-charcoal text-white font-mono text-[10px] uppercase px-2 py-1 space-x-1">
                        <span>Brand: {{ request('brand') }}</span>
                        <button type="button" @click="removeFilter('brand')" class="hover:text-brand-red ml-1">×</button>
                    </span>
                @endif

                @if(request('grade'))
                    <span class="inline-flex items-center bg-brand-charcoal text-white font-mono text-[10px] uppercase px-2 py-1 space-x-1">
                        <span>Condition: {{ request('grade') }}</span>
                        <button type="button" @click="removeFilter('grade')" class="hover:text-brand-red ml-1">×</button>
                    </span>
                @endif

                <button type="button" @click="resetFilters" class="text-[10px] font-mono uppercase font-bold text-brand-red hover:underline ml-2">
                    Clear All
                </button>
            @endif
        </div>

        <!-- Product Grid & Loading Skeleton Wrapper -->
        <div class="relative min-h-[400px]">

            <!-- Skeleton Overlay -->
            <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 bg-white">
                @for($i = 0; $i < 4; $i++)
                    <div class="border border-gray-200 p-5 flex flex-col justify-between animate-pulse">
                        <div class="flex justify-between mb-4">
                            <div class="h-4 w-16 bg-gray-200"></div>
                            <div class="h-4 w-20 bg-gray-200"></div>
                        </div>
                        <div class="aspect-[4/3] bg-gray-100 mb-5"></div>
                        <div class="space-y-2 mb-6">
                            <div class="h-3 w-24 bg-gray-200"></div>
                            <div class="h-5 w-48 bg-gray-200"></div>
                            <div class="h-3 w-32 bg-gray-200"></div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 flex justify-between">
                            <div class="h-6 w-24 bg-gray-200"></div>
                            <div class="h-8 w-16 bg-gray-200"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Product Grid -->
            <div id="product-grid" :class="{ 'opacity-0': isLoading }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 transition-opacity duration-200">
            @forelse($conditions as $condition)
                <a href="{{ route('products.show', $condition->product->slug) }}" class="group block card-glow-hover relative flex flex-col justify-between border border-gray-200 bg-white p-5 hover:-translate-y-1 hover:shadow-2xl hover:border-brand-red/50 transition-all duration-300">
                    
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
                        @if($condition->product->image)
                            <img src="{{ Storage::url($condition->product->image) }}" class="h-28 w-28 object-contain group-hover:scale-110 transition-transform duration-300" />
                        @else
                            <x-product-icon :categorySlug="$condition->product->category->slug ?? ''" class="h-28 w-28 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors duration-300 stroke-[1.5]" />
                        @endif

                        <span class="absolute bottom-2 left-2 font-mono text-[10px] uppercase tracking-wider text-brand-grey">
                            {{ strtoupper($condition->product->brand) }}
                        </span>

                        @if($condition->original_price && $condition->original_price > $condition->price)
                            @php $savings = round((($condition->original_price - $condition->price) / $condition->original_price) * 100); @endphp
                            <span class="absolute top-2 right-2 bg-brand-red text-white font-mono text-[10px] font-bold px-1.5 py-0.5 shadow">
                                SAVE {{ $savings }}%
                            </span>
                        @endif
                    </div>

                    <!-- Product Specs & Ratings -->
                    <div class="space-y-1">
                        <div class="font-mono text-xs text-brand-grey uppercase tracking-wider">
                            {{ $condition->product->category->name ?? 'SMARTPHONE' }}
                        </div>
                        <h3 class="font-sans font-bold text-brand-charcoal text-lg group-hover:text-brand-red transition-colors leading-snug block">
                            {{ $condition->product->name }}
                        </h3>
                        
                        <div class="flex items-center space-x-1 py-1">
                            <div class="flex items-center text-brand-red">
                                @php $rating = round($condition->product->reviews_avg_rating ?? 0); @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $rating)
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @else
                                        <svg class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="font-mono text-[10px] text-brand-grey">({{ $condition->product->reviews_count ?? 0 }})</span>
                        </div>

                        <p class="font-mono text-xs text-brand-grey mt-1">
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
                                @if($condition->original_price && $condition->original_price > $condition->price)
                                    <span class="font-mono text-xs text-brand-grey line-through">
                                        Rs {{ number_format($condition->original_price) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                </a>
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
        </div>

        <!-- Pagination -->
        <div id="pagination-container">
            @if($conditions->hasPages())
                <div class="mt-12" @click="handlePaginationClick">
                    {{ $conditions->links() }}
                </div>
            @endif
        </div>

        </div> <!-- End Right Content Area -->
        </div> <!-- End Flex Layout -->
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('catalogFilter', () => ({
                mobileFiltersOpen: false,
                isLoading: false,
                applyFilters() {
                    const currentUrl = new URL(window.location.href);
                    const url = new URL(this.$refs.filterForm.action);
                    
                    if (currentUrl.searchParams.has('search')) {
                        url.searchParams.set('search', currentUrl.searchParams.get('search'));
                    }

                    const formData = new FormData(this.$refs.filterForm);
                    for (const [key, value] of formData.entries()) {
                        if (value) {
                            url.searchParams.set(key, value);
                        }
                    }

                    this.fetchAndUpdate(url.toString());
                },
                removeFilter(key) {
                    const currentUrl = new URL(window.location.href);
                    if (key === 'search') {
                        currentUrl.searchParams.delete('search');
                    } else {
                        const input = this.$refs.filterForm.querySelector(`[name="${key}"]`);
                        if (input) input.value = '';
                    }
                    
                    const url = new URL(this.$refs.filterForm.action);
                    if (key !== 'search' && currentUrl.searchParams.has('search')) {
                        url.searchParams.set('search', currentUrl.searchParams.get('search'));
                    }
                    
                    const formData = new FormData(this.$refs.filterForm);
                    for (const [keyForm, value] of formData.entries()) {
                        if (value) {
                            url.searchParams.set(keyForm, value);
                        }
                    }

                    this.fetchAndUpdate(url.toString());
                },
                resetFilters() {
                    this.$refs.filterForm.reset();
                    // Dispatch change event to update Alpine/native selects
                    const selects = this.$refs.filterForm.querySelectorAll('select');
                    selects.forEach(select => {
                        select.value = '';
                    });
                    
                    const url = new URL(this.$refs.filterForm.action);
                    this.fetchAndUpdate(url.toString());
                },
                handlePaginationClick(e) {
                    const link = e.target.closest('a');
                    if (link) {
                        e.preventDefault();
                        this.fetchAndUpdate(link.href);
                    }
                },
                fetchAndUpdate(url) {
                    this.isLoading = true;
                    window.history.pushState({}, '', url);

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const newGrid = doc.querySelector('#product-grid');
                        if (newGrid) {
                            document.querySelector('#product-grid').innerHTML = newGrid.innerHTML;
                        }
                        
                        const newCount = doc.querySelector('#result-count');
                        if (newCount) {
                            document.querySelector('#result-count').innerHTML = newCount.innerHTML;
                        }

                        const newPagination = doc.querySelector('#pagination-container');
                        if (newPagination) {
                            document.querySelector('#pagination-container').innerHTML = newPagination.innerHTML;
                        }

                        const newActiveFilters = doc.querySelector('#active-filters-container');
                        if (newActiveFilters) {
                            document.querySelector('#active-filters-container').innerHTML = newActiveFilters.innerHTML;
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                }
            }));
        });
    </script>
@endsection
