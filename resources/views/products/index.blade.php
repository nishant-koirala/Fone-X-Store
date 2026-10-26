@extends('layouts.storefront')

@section('title', 'Phone Inventory & Catalog — FoneX Store')
@section('meta_description', 'Browse Fone-X-Store\'s complete inventory of certified pre-owned and new smartphones. Filter by brand, condition, and price.')
@section('meta_keywords', 'smartphones, inventory, phones, iphone, samsung, used phones, buy phones nepal')

@section('content')

@php
    $currentCategory = request('category', '');
    $currentBrand = request('brand', '');
    $currentGrade = request('grade', '');
    $currentSort = request('sort', 'newest');
    $currentSearch = request('search', '');
    $currentMinPrice = request('min_price', '');
    $currentMaxPrice = request('max_price', '');
@endphp

<div x-data="catalogFilter({
        initialCategory: '{{ $currentCategory }}',
        initialBrand: '{{ $currentBrand }}',
        initialGrade: '{{ $currentGrade }}',
        initialSort: '{{ $currentSort }}',
        initialSearch: '{{ $currentSearch }}',
        initialMinPrice: '{{ $currentMinPrice }}',
        initialMaxPrice: '{{ $currentMaxPrice }}'
    })" 
    class="min-h-screen bg-brand-white">

    <!-- Elevated Editorial Catalog Header -->
    <div class="bg-brand-charcoal text-white border-b-2 border-brand-charcoal py-10 lg:py-16 relative overflow-hidden">
        <!-- Subtle dot matrix pattern overlay -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#ffffff 2px, transparent 2px); background-size: 28px 28px;"></div>

        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-white/10">
                <div>
                    <div class="inline-flex items-center space-x-2 border border-white/20 px-3 py-1 font-mono text-[11px] font-bold uppercase tracking-widest text-brand-white bg-white/10 backdrop-blur-sm mb-4">
                        <span class="h-2 w-2 rounded-full bg-brand-red animate-pulse"></span>
                        <span>STORE INVENTORY DIRECTORY</span>
                    </div>
                    <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-white leading-none">
                        Certified Devices
                    </h1>
                    <p class="font-sans text-sm sm:text-base text-white/70 max-w-xl font-light mt-2 leading-relaxed">
                        Factory-sealed brand new models and 100-point diagnostic certified pre-owned smartphones backed by a 7-day checking guarantee.
                    </p>
                </div>

                <div class="flex flex-col md:items-end font-mono text-xs text-white/80 tracking-wider">
                    <span class="text-white/50 uppercase text-[10px]">Current Availability</span>
                    <div class="text-lg font-bold text-white mt-0.5">
                        <span id="result-count" class="text-brand-red text-2xl font-black">{{ $conditions->total() }}</span> Units Listed
                    </div>
                </div>
            </div>

            <!-- Quick Filter Preset Chips Strip -->
            <div class="mt-6 flex items-center space-x-2 sm:space-x-3 overflow-x-auto pb-2 scrollbar-thin font-mono text-xs">
                <span class="text-white/50 uppercase text-[10px] font-bold flex-shrink-0 mr-1">Quick Select:</span>
                
                <button type="button" 
                        @click="setQuickFilter('', '', '')"
                        :class="selectedCategory === '' && selectedBrand === '' && selectedGrade === '' ? 'bg-brand-red text-white border-brand-red' : 'bg-white/10 text-white/80 hover:bg-white/20 border-white/20'"
                        class="px-3.5 py-1.5 rounded-full border transition-all duration-200 flex-shrink-0 font-bold uppercase tracking-wider">
                    All Devices
                </button>

                <button type="button" 
                        @click="setQuickFilter('', 'Apple', '')"
                        :class="selectedBrand === 'Apple' ? 'bg-brand-red text-white border-brand-red' : 'bg-white/10 text-white/80 hover:bg-white/20 border-white/20'"
                        class="px-3.5 py-1.5 rounded-full border transition-all duration-200 flex-shrink-0 font-bold uppercase tracking-wider">
                    Apple iPhone
                </button>

                <button type="button" 
                        @click="setQuickFilter('', 'Samsung', '')"
                        :class="selectedBrand === 'Samsung' ? 'bg-brand-red text-white border-brand-red' : 'bg-white/10 text-white/80 hover:bg-white/20 border-white/20'"
                        class="px-3.5 py-1.5 rounded-full border transition-all duration-200 flex-shrink-0 font-bold uppercase tracking-wider">
                    Samsung Galaxy
                </button>

                <button type="button" 
                        @click="setQuickFilter('', '', 'new')"
                        :class="selectedGrade === 'new' ? 'bg-brand-red text-white border-brand-red' : 'bg-white/10 text-white/80 hover:bg-white/20 border-white/20'"
                        class="px-3.5 py-1.5 rounded-full border transition-all duration-200 flex-shrink-0 font-bold uppercase tracking-wider">
                    Brand New Sealed
                </button>

                <button type="button" 
                        @click="setQuickFilter('', '', 'used')"
                        :class="selectedGrade === 'used' || selectedGrade === 'A' || selectedGrade === 'B' ? 'bg-brand-red text-white border-brand-red' : 'bg-white/10 text-white/80 hover:bg-white/20 border-white/20'"
                        class="px-3.5 py-1.5 rounded-full border transition-all duration-200 flex-shrink-0 font-bold uppercase tracking-wider">
                    Certified Pre-Owned
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Catalog Main Container -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-8 lg:py-12 bg-brand-offwhite/50">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Left Sidebar (Sticky on Desktop) -->
            <div class="w-full lg:w-72 flex-shrink-0 lg:sticky lg:top-24 z-30">

                <!-- Mobile Filter Trigger Button -->
                <div class="lg:hidden mb-4">
                    <button type="button" 
                            @click="mobileFiltersOpen = true" 
                            class="w-full font-mono text-xs uppercase font-bold text-brand-charcoal bg-brand-white border-2 border-brand-charcoal rounded-lg py-3.5 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:bg-brand-charcoal hover:text-white transition-colors flex items-center justify-center space-x-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter & Sort Options</span>
                    </button>
                </div>

                <!-- Modern Tactile Filter Form -->
                <form x-ref="filterForm" 
                      @submit.prevent="applyFilters" 
                      method="GET" 
                      action="{{ route('products.index') }}" 
                      :class="mobileFiltersOpen ? 'fixed inset-0 z-[100] flex flex-col justify-end bg-brand-charcoal/80 backdrop-blur-sm' : 'hidden lg:block'"
                      class="lg:block">
                    
                    <!-- Hidden input bindings for full AJAX query synchronization -->
                    <input type="hidden" name="category" :value="selectedCategory">
                    <input type="hidden" name="brand" :value="selectedBrand">
                    <input type="hidden" name="grade" :value="selectedGrade">
                    <input type="hidden" name="sort" :value="selectedSort">
                    <input type="hidden" name="min_price" :value="selectedMinPrice">
                    <input type="hidden" name="max_price" :value="selectedMaxPrice">
                    <input type="hidden" name="search" :value="selectedSearch">

                    <!-- Mobile Overlay Click-to-Close -->
                    <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="absolute inset-0 lg:hidden"></div>

                    <div :class="mobileFiltersOpen ? 'relative bg-brand-white w-full border-t-2 border-brand-charcoal p-6 max-h-[85vh] overflow-y-auto transform transition-transform rounded-t-2xl' : 'bg-brand-white p-6 border-2 border-brand-charcoal rounded-xl shadow-[6px_6px_0px_0px_rgba(42,42,44,1)]'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        
                        <!-- Mobile Modal Header -->
                        <div class="flex items-center justify-between mb-6 lg:hidden pb-4 border-b-2 border-brand-charcoal">
                            <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Filter Inventory</h2>
                            <button type="button" @click="mobileFiltersOpen = false" class="p-2 border-2 border-brand-charcoal hover:bg-brand-red hover:text-white rounded transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sidebar Heading (Desktop) -->
                        <div class="hidden lg:flex items-center justify-between pb-4 mb-5 border-b-2 border-brand-charcoal">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">Filters & Refinements</span>
                            <button type="button" 
                                    x-show="hasActiveFilters" 
                                    @click="resetFilters()" 
                                    class="font-mono text-[11px] font-bold uppercase text-brand-red hover:underline">
                                Reset
                            </button>
                        </div>

                        <div class="flex flex-col space-y-6">
                            
                            <!-- 1. Condition Grade Filter (Interactive Pills) -->
                            <div>
                                <label class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2.5">
                                    Condition Grade
                                </label>
                                <div class="space-y-1.5 font-mono text-xs">
                                    <button type="button" 
                                            @click="setGrade('')"
                                            :class="selectedGrade === '' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded border text-left transition-colors">
                                        <span>All Conditions</span>
                                        <span class="text-[10px]">Any</span>
                                    </button>
                                    <button type="button" 
                                            @click="setGrade('new')"
                                            :class="selectedGrade === 'new' ? 'border-emerald-600 bg-emerald-600 text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-emerald-600'"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded border text-left transition-colors">
                                        <span class="flex items-center space-x-2">
                                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                            <span>Brand New Sealed</span>
                                        </span>
                                        <span class="text-[10px] uppercase">Box</span>
                                    </button>
                                    <button type="button" 
                                            @click="setGrade('A')"
                                            :class="selectedGrade === 'A' ? 'border-blue-600 bg-blue-600 text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-blue-600'"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded border text-left transition-colors">
                                        <span class="flex items-center space-x-2">
                                            <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                            <span>Grade A (Pristine)</span>
                                        </span>
                                        <span class="text-[10px] uppercase">90%+</span>
                                    </button>
                                    <button type="button" 
                                            @click="setGrade('B')"
                                            :class="selectedGrade === 'B' ? 'border-amber-600 bg-amber-600 text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-amber-600'"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded border text-left transition-colors">
                                        <span class="flex items-center space-x-2">
                                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                            <span>Grade B (Excellent)</span>
                                        </span>
                                        <span class="text-[10px] uppercase">Popular</span>
                                    </button>
                                    <button type="button" 
                                            @click="setGrade('C')"
                                            :class="selectedGrade === 'C' ? 'border-stone-600 bg-stone-600 text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-stone-600'"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded border text-left transition-colors">
                                        <span class="flex items-center space-x-2">
                                            <span class="h-2 w-2 rounded-full bg-stone-400"></span>
                                            <span>Grade C (Fair Value)</span>
                                        </span>
                                        <span class="text-[10px] uppercase">Value</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Brand Selector (Pills Grid) -->
                            <div class="pt-4 border-t border-brand-charcoal/15">
                                <label class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2.5">
                                    Brand
                                </label>
                                <div class="flex flex-wrap gap-1.5 font-mono text-xs">
                                    <button type="button" 
                                            @click="setBrand('')"
                                            :class="selectedBrand === '' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="px-2.5 py-1.5 rounded border transition-colors">
                                        All
                                    </button>
                                    @foreach($brands as $brand)
                                        <button type="button" 
                                                @click="setBrand('{{ $brand }}')"
                                                :class="selectedBrand === '{{ $brand }}' ? 'border-brand-red bg-brand-red text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                                class="px-2.5 py-1.5 rounded border transition-colors">
                                            {{ $brand }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 3. Category Selector -->
                            <div class="pt-4 border-t border-brand-charcoal/15">
                                <label for="category-select" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">
                                    Device Category
                                </label>
                                <select id="category-select" 
                                        @change="setCategory($event.target.value)" 
                                        class="w-full font-mono text-xs font-bold border-2 border-brand-charcoal rounded-lg bg-brand-white text-brand-charcoal px-3 py-2.5 focus:border-brand-red focus:ring-0 outline-none cursor-pointer">
                                    <option value="" :selected="selectedCategory === ''">All Categories</option>
                                    @foreach($categories as $cat)
                                        @php
                                            $slug = is_object($cat) ? $cat->slug : ($cat['slug'] ?? $cat);
                                            $name = is_object($cat) ? $cat->name : ($cat['name'] ?? $cat);
                                        @endphp
                                        <option value="{{ $slug }}" :selected="selectedCategory === '{{ $slug }}'">
                                            {{ strtoupper($name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 4. Budget / Price Range Presets -->
                            <div class="pt-4 border-t border-brand-charcoal/15">
                                <label class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2.5">
                                    Price Range (NPR)
                                </label>
                                <div class="grid grid-cols-2 gap-1.5 font-mono text-[11px] mb-3">
                                    <button type="button" 
                                            @click="setPriceRange('', '50000')"
                                            :class="selectedMaxPrice === '50000' && selectedMinPrice === '' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="py-1.5 px-2 rounded border text-center transition-colors">
                                        &lt; Rs 50K
                                    </button>
                                    <button type="button" 
                                            @click="setPriceRange('50000', '100000')"
                                            :class="selectedMinPrice === '50000' && selectedMaxPrice === '100000' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="py-1.5 px-2 rounded border text-center transition-colors">
                                        50K – 100K
                                    </button>
                                    <button type="button" 
                                            @click="setPriceRange('100000', '150000')"
                                            :class="selectedMinPrice === '100000' && selectedMaxPrice === '150000' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="py-1.5 px-2 rounded border text-center transition-colors">
                                        100K – 150K
                                    </button>
                                    <button type="button" 
                                            @click="setPriceRange('150000', '')"
                                            :class="selectedMinPrice === '150000' && selectedMaxPrice === '' ? 'border-brand-charcoal bg-brand-charcoal text-white font-bold' : 'border-brand-charcoal/20 bg-brand-offwhite text-brand-charcoal hover:border-brand-charcoal'"
                                            class="py-1.5 px-2 rounded border text-center transition-colors">
                                        Rs 150K+
                                    </button>
                                </div>

                                <div class="flex items-center space-x-2 font-mono text-xs">
                                    <input type="number" 
                                           placeholder="Min Rs" 
                                           :value="selectedMinPrice" 
                                           @input.debounce.500ms="selectedMinPrice = $event.target.value; applyFilters();"
                                           class="w-1/2 bg-brand-white border border-brand-charcoal/30 rounded px-2.5 py-1.5 text-xs focus:border-brand-red outline-none" />
                                    <span class="text-brand-grey">–</span>
                                    <input type="number" 
                                           placeholder="Max Rs" 
                                           :value="selectedMaxPrice" 
                                           @input.debounce.500ms="selectedMaxPrice = $event.target.value; applyFilters();"
                                           class="w-1/2 bg-brand-white border border-brand-charcoal/30 rounded px-2.5 py-1.5 text-xs focus:border-brand-red outline-none" />
                                </div>
                            </div>

                            <!-- Mobile Apply Button -->
                            <div class="mt-4 pt-4 border-t border-brand-charcoal/15 lg:hidden">
                                <button type="button" 
                                        @click="mobileFiltersOpen = false" 
                                        class="w-full font-mono text-xs uppercase font-bold text-white bg-brand-red py-3.5 rounded-lg shadow-sm">
                                    View Matching Units
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Content Area: Top Toolbar, Active Filters, Products Grid, Pagination -->
            <div class="flex-grow min-w-0">

                <!-- Top Toolbar: Search Input + Sorting Selector -->
                <div class="bg-brand-white border-2 border-brand-charcoal rounded-xl p-4 mb-6 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                    
                    <!-- Search Input -->
                    <div class="relative flex-grow max-w-md">
                        <label for="catalog-search" class="sr-only">Search smartphones</label>
                        <input type="text" 
                               id="catalog-search"
                               placeholder="Search iPhone, Samsung S24, Pixel..." 
                               :value="selectedSearch" 
                               @input.debounce.400ms="selectedSearch = $event.target.value; applyFilters();"
                               class="w-full bg-brand-offwhite/60 border border-brand-charcoal/20 rounded-lg pl-9 pr-8 py-2.5 font-sans text-xs sm:text-sm text-brand-charcoal placeholder-brand-grey focus:border-brand-red focus:bg-white outline-none transition-colors" />
                        
                        <svg class="h-4 w-4 text-brand-grey absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <button type="button" 
                                x-show="selectedSearch" 
                                @click="selectedSearch = ''; applyFilters();" 
                                aria-label="Clear search" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-brand-grey hover:text-brand-charcoal font-bold p-1">
                            ✕
                        </button>
                    </div>

                    <!-- Sort Segmented Dropdown -->
                    <div class="flex items-center space-x-2 font-mono text-xs flex-shrink-0">
                        <span class="text-brand-grey uppercase font-bold text-[11px] hidden sm:inline">Sort:</span>
                        <select @change="setSort($event.target.value)" 
                                class="bg-brand-offwhite/60 border border-brand-charcoal/20 rounded-lg px-3 py-2.5 font-mono text-xs font-bold text-brand-charcoal focus:border-brand-red outline-none cursor-pointer">
                            <option value="newest" :selected="selectedSort === 'newest'">Newest Listed</option>
                            <option value="price_asc" :selected="selectedSort === 'price_asc'">Price: Low to High</option>
                            <option value="price_desc" :selected="selectedSort === 'price_desc'">Price: High to Low</option>
                        </select>
                    </div>

                </div>

                <!-- Active Filter Badges Container -->
                <div id="active-filters-container" class="mb-6 flex flex-wrap items-center gap-2 min-h-[28px]">
                    @if(request()->anyFilled(['category', 'brand', 'grade', 'search', 'min_price', 'max_price']))
                        <span class="font-mono text-[11px] font-bold uppercase text-brand-grey mr-1">Active:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center space-x-1.5 bg-brand-white border border-brand-charcoal/30 rounded-full text-brand-charcoal font-mono text-xs font-bold px-3 py-1 shadow-xs">
                                <span>Search: "{{ request('search') }}"</span>
                                <button type="button" @click="selectedSearch = ''; applyFilters();" class="text-brand-grey hover:text-brand-red font-bold ml-1">✕</button>
                            </span>
                        @endif

                        @if(request('brand'))
                            <span class="inline-flex items-center space-x-1.5 bg-brand-white border border-brand-charcoal/30 rounded-full text-brand-charcoal font-mono text-xs font-bold px-3 py-1 shadow-xs">
                                <span>Brand: {{ request('brand') }}</span>
                                <button type="button" @click="selectedBrand = ''; applyFilters();" class="text-brand-grey hover:text-brand-red font-bold ml-1">✕</button>
                            </span>
                        @endif

                        @if(request('grade'))
                            <span class="inline-flex items-center space-x-1.5 bg-brand-white border border-brand-charcoal/30 rounded-full text-brand-charcoal font-mono text-xs font-bold px-3 py-1 shadow-xs">
                                <span>Grade: {{ strtoupper(request('grade')) }}</span>
                                <button type="button" @click="selectedGrade = ''; applyFilters();" class="text-brand-grey hover:text-brand-red font-bold ml-1">✕</button>
                            </span>
                        @endif

                        @if(request('category'))
                            @php 
                                $cat = collect($categories)->first(function($c) {
                                    return (is_object($c) ? $c->slug : $c['slug']) === request('category');
                                });
                                $catName = $cat ? (is_object($cat) ? $cat->name : $cat['name']) : request('category');
                            @endphp
                            <span class="inline-flex items-center space-x-1.5 bg-brand-white border border-brand-charcoal/30 rounded-full text-brand-charcoal font-mono text-xs font-bold px-3 py-1 shadow-xs">
                                <span>Category: {{ $catName }}</span>
                                <button type="button" @click="selectedCategory = ''; applyFilters();" class="text-brand-grey hover:text-brand-red font-bold ml-1">✕</button>
                            </span>
                        @endif

                        @if(request('min_price') || request('max_price'))
                            <span class="inline-flex items-center space-x-1.5 bg-brand-white border border-brand-charcoal/30 rounded-full text-brand-charcoal font-mono text-xs font-bold px-3 py-1 shadow-xs">
                                <span>Rs {{ is_numeric(request('min_price')) ? number_format((float) request('min_price')) : '0' }} – {{ is_numeric(request('max_price')) ? number_format((float) request('max_price')) : (request('max_price') ?: 'Max') }}</span>
                                <button type="button" @click="selectedMinPrice = ''; selectedMaxPrice = ''; applyFilters();" class="text-brand-grey hover:text-brand-red font-bold ml-1">✕</button>
                            </span>
                        @endif


                        <button type="button" @click="resetFilters()" class="font-mono text-xs uppercase font-bold text-brand-red hover:underline ml-2">
                            Clear All
                        </button>
                    @endif
                </div>

                <!-- Product Grid & Loading Skeleton Wrapper -->
                <div class="relative min-h-[420px]">

                    <!-- Smooth Skeleton Overlay during AJAX fetch -->
                    <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 bg-brand-offwhite/80 backdrop-blur-xs">
                        @for($i = 0; $i < 6; $i++)
                            <div class="border-2 border-brand-charcoal/10 bg-white rounded-xl p-5 flex flex-col justify-between animate-pulse">
                                <div class="flex justify-between mb-4">
                                    <div class="h-5 w-20 bg-gray-200 rounded"></div>
                                    <div class="h-5 w-16 bg-gray-200 rounded"></div>
                                </div>
                                <div class="aspect-[4/3] bg-gray-100 rounded-lg mb-4"></div>
                                <div class="space-y-2 mb-4">
                                    <div class="h-3 w-16 bg-gray-200 rounded"></div>
                                    <div class="h-5 w-40 bg-gray-200 rounded"></div>
                                </div>
                                <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                                    <div class="h-6 w-24 bg-gray-200 rounded"></div>
                                    <div class="h-7 w-16 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <!-- Products Grid -->
                    <div id="product-grid" aria-live="polite" :class="{ 'opacity-30': isLoading }" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 transition-opacity duration-200">
                        @forelse($conditions as $condition)
                            <x-product-card :condition="$condition" />
                        @empty
                            <!-- Empty State Card -->
                            <div class="col-span-full border-2 border-brand-charcoal rounded-xl p-12 text-center bg-brand-white shadow-[6px_6px_0px_0px_rgba(42,42,44,1)]">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-offwhite border-2 border-brand-charcoal text-brand-red text-2xl font-bold mb-4">
                                    🔍
                                </div>
                                <h3 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-brand-charcoal">
                                    No Matching Units Found
                                </h3>
                                <p class="mt-2 font-sans text-xs sm:text-sm text-brand-charcoal/70 max-w-md mx-auto font-light leading-relaxed">
                                    We couldn't find any certified devices matching your active filters. Try adjusting your brand, condition grade, or budget.
                                </p>
                                <div class="mt-6">
                                    <button type="button" 
                                            @click="resetFilters()" 
                                            class="inline-flex items-center space-x-2 font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red px-6 py-3 rounded-lg shadow-[3px_3px_0px_0px_rgba(200,32,45,1)] transition-colors">
                                        <span>Reset All Filters</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                </div>

                <!-- Pagination Container -->
                <div id="pagination-container">
                    @if($conditions->hasPages())
                        <div class="mt-12 pt-6 border-t-2 border-brand-charcoal/20" @click="handlePaginationClick">
                            {{ $conditions->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>

</div>

<!-- Alpine Catalog Filter Script -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('catalogFilter', (config) => ({
            mobileFiltersOpen: false,
            isLoading: false,
            selectedCategory: config.initialCategory || '',
            selectedBrand: config.initialBrand || '',
            selectedGrade: config.initialGrade || '',
            selectedSort: config.initialSort || 'newest',
            selectedSearch: config.initialSearch || '',
            selectedMinPrice: config.initialMinPrice || '',
            selectedMaxPrice: config.initialMaxPrice || '',

            get hasActiveFilters() {
                return this.selectedCategory !== '' || 
                       this.selectedBrand !== '' || 
                       this.selectedGrade !== '' || 
                       this.selectedSearch !== '' || 
                       this.selectedMinPrice !== '' || 
                       this.selectedMaxPrice !== '';
            },

            setGrade(grade) {
                this.selectedGrade = grade;
                this.applyFilters();
            },

            setBrand(brand) {
                this.selectedBrand = brand;
                this.applyFilters();
            },

            setCategory(category) {
                this.selectedCategory = category;
                this.applyFilters();
            },

            setSort(sort) {
                this.selectedSort = sort;
                this.applyFilters();
            },

            setPriceRange(min, max) {
                this.selectedMinPrice = min;
                this.selectedMaxPrice = max;
                this.applyFilters();
            },

            setQuickFilter(cat, brand, grade) {
                this.selectedCategory = cat;
                this.selectedBrand = brand;
                this.selectedGrade = grade;
                this.applyFilters();
            },

            applyFilters() {
                const url = new URL(this.$refs.filterForm.action);
                
                if (this.selectedCategory) url.searchParams.set('category', this.selectedCategory);
                if (this.selectedBrand) url.searchParams.set('brand', this.selectedBrand);
                if (this.selectedGrade) url.searchParams.set('grade', this.selectedGrade);
                if (this.selectedSort && this.selectedSort !== 'newest') url.searchParams.set('sort', this.selectedSort);
                if (this.selectedSearch) url.searchParams.set('search', this.selectedSearch);
                if (this.selectedMinPrice) url.searchParams.set('min_price', this.selectedMinPrice);
                if (this.selectedMaxPrice) url.searchParams.set('max_price', this.selectedMaxPrice);

                this.fetchAndUpdate(url.toString());
            },

            resetFilters() {
                this.selectedCategory = '';
                this.selectedBrand = '';
                this.selectedGrade = '';
                this.selectedSort = 'newest';
                this.selectedSearch = '';
                this.selectedMinPrice = '';
                this.selectedMaxPrice = '';
                
                const url = new URL(this.$refs.filterForm.action);
                this.fetchAndUpdate(url.toString());
            },

            handlePaginationClick(e) {
                const link = e.target.closest('a');
                if (link) {
                    e.preventDefault();
                    this.fetchAndUpdate(link.href);
                    window.scrollTo({ top: 300, behavior: 'smooth' });
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
