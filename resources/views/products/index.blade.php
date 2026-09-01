@extends('layouts.storefront')

@section('title', 'Phone Inventory & Catalog — FoneX Store')

@section('content')

    <!-- Catalog Page Header -->
    <div x-data="catalogFilter()" class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12 lg:py-24">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] mb-6">
                        STORE CATALOG
                    </span>
                    <h1 class="font-display text-5xl sm:text-7xl uppercase tracking-tight text-brand-white leading-none">
                        Phone Inventory
                    </h1>
                </div>
                <div class="font-mono text-xs text-brand-white/70 tracking-widest">
                    SHOWING <span id="result-count" class="font-bold text-brand-red">{{ $conditions->total() }}</span> AVAILABLE LISTINGS
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Catalog Main Container -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-10 bg-brand-offwhite" x-data="catalogFilter()">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Left Sidebar (Sticky on Desktop) -->
            <div class="w-full lg:w-64 flex-shrink-0 lg:sticky lg:top-28 z-30">

                <!-- Mobile Filter Toggle Button -->
                <div class="lg:hidden mb-6">
                    <button type="button" @click="mobileFiltersOpen = true" class="w-full font-mono text-xs uppercase font-bold text-brand-charcoal bg-brand-white border-2 border-brand-charcoal py-4 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:bg-brand-charcoal hover:text-brand-white transition-colors flex items-center justify-center space-x-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter & Sort Options</span>
                    </button>
                </div>

                <!-- Stark Filter Sidebar -->
                <form x-ref="filterForm" @submit.prevent="applyFilters" @change="applyFilters" method="GET" action="{{ route('products.index') }}" 
                      :class="mobileFiltersOpen ? 'fixed inset-0 z-[100] flex flex-col justify-end bg-brand-charcoal/80 backdrop-blur-sm' : 'hidden lg:block'"
                      class="lg:block">
            
            <!-- Mobile Overlay Click-to-Close -->
            <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="absolute inset-0 lg:hidden"></div>

            <div :class="mobileFiltersOpen ? 'relative bg-brand-white w-full border-t-2 border-brand-charcoal p-6 max-h-[85vh] overflow-y-auto transform transition-transform' : 'bg-brand-white p-6 border-2 border-brand-charcoal shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                
                <!-- Mobile Header -->
                <div class="flex items-center justify-between mb-8 lg:hidden pb-4 border-b-2 border-brand-charcoal">
                    <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Filters</h2>
                    <button type="button" @click="mobileFiltersOpen = false" class="p-2 border-2 border-brand-charcoal hover:bg-brand-red hover:text-brand-white hover:border-brand-red transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col gap-6">
                
                <!-- Category Filter -->
                <div>
                    <label for="filter-category" class="font-mono text-[10px] font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Category</label>
                    <select id="filter-category" name="category" class="w-full font-mono font-bold text-xs border-2 border-brand-charcoal bg-brand-white text-brand-charcoal px-3 py-3 focus:border-brand-red focus:ring-0 appearance-none rounded-none cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            @php
                                $slug = is_object($cat) ? $cat->slug : ($cat['slug'] ?? $cat);
                                $name = is_object($cat) ? $cat->name : ($cat['name'] ?? $cat);
                            @endphp
                            <option value="{{ $slug }}" {{ request('category') == $slug ? 'selected' : '' }}>
                                {{ strtoupper($name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label for="filter-brand" class="font-mono text-[10px] font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Brand</label>
                    <select id="filter-brand" name="brand" class="w-full font-mono font-bold text-xs border-2 border-brand-charcoal bg-brand-white text-brand-charcoal px-3 py-3 focus:border-brand-red focus:ring-0 appearance-none rounded-none cursor-pointer">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ strtoupper($brand) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Condition Grade Filter -->
                <div>
                    <label for="filter-grade" class="font-mono text-[10px] font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Condition</label>
                    <select id="filter-grade" name="grade" class="w-full font-mono font-bold text-xs border-2 border-brand-charcoal bg-brand-white text-brand-charcoal px-3 py-3 focus:border-brand-red focus:ring-0 appearance-none rounded-none cursor-pointer">
                        <option value="">All Conditions</option>
                        <option value="new" {{ request('grade') == 'new' ? 'selected' : '' }}>New Only</option>
                        <option value="used" {{ request('grade') == 'used' ? 'selected' : '' }}>All Pre-Owned</option>
                        <option value="A" {{ request('grade') == 'A' ? 'selected' : '' }}>Grade A</option>
                        <option value="B" {{ request('grade') == 'B' ? 'selected' : '' }}>Grade B</option>
                        <option value="C" {{ request('grade') == 'C' ? 'selected' : '' }}>Grade C</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div>
                    <label for="filter-sort" class="font-mono text-[10px] font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Sort By</label>
                    <select id="filter-sort" name="sort" class="w-full font-mono font-bold text-xs border-2 border-brand-charcoal bg-brand-white text-brand-charcoal px-3 py-3 focus:border-brand-red focus:ring-0 appearance-none rounded-none cursor-pointer">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Listed</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-2 pt-4 border-t-2 border-brand-charcoal">
                    <button type="submit" class="w-full font-mono text-xs uppercase font-bold text-brand-white bg-brand-charcoal hover:bg-brand-red py-4 transition-colors">
                        Apply Filters
                    </button>
                    @if(request()->anyFilled(['category', 'brand', 'grade', 'sort', 'min_price', 'max_price', 'search']))
                        <a href="{{ route('products.index') }}" @click.prevent="resetFilters" class="w-full font-mono text-xs uppercase font-bold text-brand-charcoal border-2 border-brand-charcoal bg-brand-white hover:bg-brand-charcoal hover:text-brand-white py-3 transition-colors text-center">
                            Clear All
                        </a>
                    @endif
                </div>

                <!-- Mobile Apply Button -->
                <div class="mt-4 lg:hidden">
                    <button type="button" @click="mobileFiltersOpen = false" class="w-full font-mono text-xs uppercase font-bold text-brand-white bg-brand-red hover:bg-brand-charcoal py-4 transition-colors border-2 border-brand-red hover:border-brand-charcoal">
                        Show Results
                    </button>
                </div>

            </div>
            </div> <!-- End panel -->
        </form>
        </div> <!-- End Left Sidebar -->

        <!-- Right Content Area -->
        <div class="flex-grow min-w-0">

        <!-- Active Filter Chips -->
        <div id="active-filters-container" class="mb-8 flex flex-wrap items-center gap-3 min-h-[28px]">
            @if(request()->anyFilled(['category', 'brand', 'grade', 'search']))
                
                @if(request('search'))
                    <span class="inline-flex items-center bg-brand-white border-2 border-brand-charcoal text-brand-charcoal font-mono text-[10px] font-bold uppercase tracking-widest pl-3 pr-1 py-1 space-x-2">
                        <span>SEARCH: {{ request('search') }}</span>
                        <button type="button" @click="removeFilter('search')" class="hover:bg-brand-red hover:text-brand-white border-l-2 border-brand-charcoal pl-2 p-1">✕</button>
                    </span>
                @endif

                @if(request('category'))
                    @php 
                        $cat = collect($categories)->first(function($c) {
                            return (is_object($c) ? $c->slug : $c['slug']) === request('category');
                        });
                        $catName = $cat ? (is_object($cat) ? $cat->name : $cat['name']) : request('category');
                    @endphp
                    <span class="inline-flex items-center bg-brand-white border-2 border-brand-charcoal text-brand-charcoal font-mono text-[10px] font-bold uppercase tracking-widest pl-3 pr-1 py-1 space-x-2">
                        <span>CAT: {{ $catName }}</span>
                        <button type="button" @click="removeFilter('category')" class="hover:bg-brand-red hover:text-brand-white border-l-2 border-brand-charcoal pl-2 p-1">✕</button>
                    </span>
                @endif

                @if(request('brand'))
                    <span class="inline-flex items-center bg-brand-white border-2 border-brand-charcoal text-brand-charcoal font-mono text-[10px] font-bold uppercase tracking-widest pl-3 pr-1 py-1 space-x-2">
                        <span>BRAND: {{ request('brand') }}</span>
                        <button type="button" @click="removeFilter('brand')" class="hover:bg-brand-red hover:text-brand-white border-l-2 border-brand-charcoal pl-2 p-1">✕</button>
                    </span>
                @endif

                @if(request('grade'))
                    <span class="inline-flex items-center bg-brand-white border-2 border-brand-charcoal text-brand-charcoal font-mono text-[10px] font-bold uppercase tracking-widest pl-3 pr-1 py-1 space-x-2">
                        <span>GRADE: {{ request('grade') }}</span>
                        <button type="button" @click="removeFilter('grade')" class="hover:bg-brand-red hover:text-brand-white border-l-2 border-brand-charcoal pl-2 p-1">✕</button>
                    </span>
                @endif
            @endif
        </div>

        <!-- Product Grid & Loading Skeleton Wrapper -->
        <div class="relative min-h-[400px]">

            <!-- Skeleton Overlay -->
            <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 bg-brand-offwhite">
                @for($i = 0; $i < 6; $i++)
                    <div class="border-2 border-gray-200 bg-white p-5 flex flex-col justify-between animate-pulse">
                        <div class="flex justify-between mb-4">
                            <div class="h-6 w-16 bg-gray-200"></div>
                            <div class="h-6 w-20 bg-gray-200"></div>
                        </div>
                        <div class="aspect-[4/3] bg-gray-100 mb-5"></div>
                        <div class="space-y-2 mb-6">
                            <div class="h-3 w-24 bg-gray-200"></div>
                            <div class="h-5 w-48 bg-gray-200"></div>
                            <div class="h-3 w-32 bg-gray-200"></div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 flex justify-between">
                            <div class="h-8 w-24 bg-gray-200"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Product Grid -->
            <div id="product-grid" :class="{ 'opacity-0': isLoading }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 transition-opacity duration-200">
            @forelse($conditions as $index => $condition)
                <x-product-card :condition="$condition" />
            @empty
                <!-- Empty State Message -->
                <div class="col-span-full border-4 border-brand-charcoal p-16 text-center bg-brand-white shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <svg class="mx-auto h-16 w-16 text-brand-charcoal stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="mt-6 font-display text-3xl uppercase tracking-tight text-brand-charcoal">Zero Matches Found</h3>
                    <p class="mt-4 font-sans text-brand-charcoal/80 max-w-md mx-auto">
                        We couldn't find any devices matching your exact filter combination. 
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-2 font-mono text-xs uppercase font-bold text-brand-white bg-brand-red hover:bg-brand-charcoal px-8 py-4 transition-colors">
                            <span>Clear Filters</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination-container">
            @if($conditions->hasPages())
                <div class="mt-16 border-t-2 border-brand-charcoal pt-8" @click="handlePaginationClick">
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
