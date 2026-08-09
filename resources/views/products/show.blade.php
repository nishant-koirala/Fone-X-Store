@extends('layouts.storefront')

@section('title', $product->name . ' — FoneX Store')

@section('content')

    <!-- Breadcrumb Header -->
    <div class="bg-brand-offwhite border-b border-gray-200 py-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 font-mono text-xs text-brand-grey uppercase">
                <a href="{{ route('home') }}" class="hover:text-brand-red transition-colors">Home</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-brand-red transition-colors">Inventory</a>
                <span>/</span>
                <span class="text-brand-charcoal font-semibold">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Detail Stage Container -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start" 
             x-data="{ 
                 conditions: {{ json_encode($product->conditions) }},
                 activeIndex: 0,
                 showStickyCart: false,
                 get activeCondition() {
                     return this.conditions[this.activeIndex] || {};
                 },
                 formatRupees(val) {
                     return Number(val || 0).toLocaleString('en-US');
                 },
                 checkSticky() {
                     if (!this.$refs.addToCartContainer) return;
                     const rect = this.$refs.addToCartContainer.getBoundingClientRect();
                     this.showStickyCart = rect.bottom < 0;
                 }
             }"
             @scroll.window="checkSticky"
             @resize.window="checkSticky">

            <!-- Left Column: Interactive 3D Phone Presentation Stage -->
            <div class="lg:col-span-6 relative">
                <div class="aspect-square flex items-center justify-center bg-brand-offwhite border border-gray-200 p-12 relative overflow-hidden shadow-sm">
                    
                    <!-- Ambient Red Glow Pedestal -->
                    <div class="absolute bottom-4 h-32 w-64 bg-brand-red/20 blur-3xl rounded-full"></div>

                    <!-- Levitating Phone Contour -->
                    <x-product-icon :categorySlug="$product->category->slug ?? ''" class="animate-float h-64 w-64 text-brand-charcoal/30 stroke-[1.5] relative z-10" />

                    <!-- Overlay Brand Tag -->
                    <div class="absolute bottom-4 left-4 font-mono text-xs font-bold uppercase tracking-wider text-brand-grey border border-gray-200 bg-white/90 backdrop-blur px-3 py-1.5 shadow-sm">
                        {{ strtoupper($product->brand) }} • OFFICIAL DESIGN
                    </div>

                    <!-- Grade Indicator -->
                    <div class="absolute top-4 right-4 bg-brand-red text-white font-mono text-xs font-bold uppercase px-3 py-1 shadow">
                        <span x-text="activeCondition.grade === 'new' ? 'BRAND NEW' : 'GRADE ' + activeCondition.grade.toUpperCase()"></span>
                    </div>

                </div>
            </div>

            <!-- Right Column: Product Details & Dynamic Condition Tabs -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Category & Brand -->
                <div class="flex items-center space-x-3">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red bg-brand-red/10 px-2.5 py-1 border border-brand-red/20">
                        {{ $product->category->name ?? 'SMARTPHONE' }}
                    </span>
                    <span class="font-mono text-xs font-semibold text-brand-grey uppercase">
                        BRAND: {{ strtoupper($product->brand) }}
                    </span>
                </div>

                <!-- Product Name -->
                <h1 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-brand-charcoal leading-none">
                    {{ $product->name }}
                </h1>

                <!-- Condition Selector Section -->
                <div class="border-t border-b border-gray-200 py-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal">Select Condition Grade</label>
                        <span class="font-mono text-xs text-brand-grey">
                            Base MSRP: Rs {{ number_format($product->base_price) }}
                        </span>
                    </div>

                    <!-- Grade Selector Buttons -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <template x-for="(cond, idx) in conditions" :key="cond.id">
                            <button type="button" 
                                    @click="activeIndex = idx"
                                    :class="activeIndex === idx ? 'border-brand-red bg-brand-red text-white shadow-md shadow-brand-red/20 scale-[1.02]' : 'border-gray-200 bg-white text-brand-charcoal hover:border-brand-red'"
                                    class="border-2 p-3 text-center transition-all duration-200 focus:outline-none">
                                <div class="font-mono text-xs font-bold uppercase" x-text="cond.grade === 'new' ? 'NEW' : 'GRADE ' + cond.grade.toUpperCase()"></div>
                                <div class="font-mono text-[11px] mt-1" :class="activeIndex === idx ? 'text-white/90' : 'text-brand-grey'" x-text="'Rs ' + formatRupees(cond.price)"></div>
                            </button>
                        </template>
                    </div>

                    <!-- Dynamic Price & Stock Display -->
                    <div class="pt-4 flex items-baseline justify-between">
                        <div>
                            <span class="font-mono text-xs text-brand-grey uppercase block">Active Grade Price</span>
                            <div class="flex items-baseline space-x-3">
                                <span class="font-mono font-bold text-brand-charcoal text-3xl sm:text-4xl">
                                    Rs <span x-text="formatRupees(activeCondition.price)"></span>
                                </span>
                                <template x-if="Number(activeCondition.price) < Number({{ $product->base_price }})">
                                    <span class="font-mono text-sm text-brand-grey line-through">
                                        Rs {{ number_format($product->base_price) }}
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Dynamic Stock Badge with Live Dot -->
                        <div>
                            <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                                <span class="font-mono text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 inline-flex items-center space-x-1.5">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>IN STOCK (<span x-text="activeCondition.quantity_in_stock"></span> available)</span>
                                </span>
                            </template>
                            <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                                <span class="font-mono text-xs font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 inline-block">
                                    ✕ OUT OF STOCK
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Add to Cart Action Form -->
                <div x-ref="addToCartContainer">
                    <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                        <form method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_condition_id" :value="activeCondition.id">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 shadow-lg shadow-brand-red/25 transition-all duration-200 flex items-center justify-center space-x-2">
                                <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add Selected Grade to Cart</span>
                            </button>
                        </form>
                    </template>

                    <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                        <button type="button" disabled class="w-full bg-gray-200 text-gray-400 border border-gray-300 font-mono text-xs uppercase font-bold tracking-wider py-4 cursor-not-allowed">
                            OUT OF STOCK — CONDITION UNAVAILABLE
                        </button>
                    </template>
                </div>

                <!-- Description Block -->
                <div class="pt-6 border-t border-gray-200 space-y-2">
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal">Product Description</h3>
                    <p class="font-sans text-sm text-brand-grey leading-relaxed">
                        {{ $product->description ?? 'No specific description provided for this phone model.' }}
                    </p>
                </div>

                <!-- Glassmorphism Trust Guarantees List -->
                <div class="glass-panel p-4 space-y-2 font-mono text-xs text-brand-charcoal shadow-sm">
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span x-text="activeCondition.grade === 'new' ? '1-Year Official Brand Warranty' : '7-Day Check Time / Testing Guarantee for Used Devices'"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span>Full 100-Point Hardware & Battery Health Diagnostic</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span>Verified IMEI & Clean Device History Guarantee</span>
                    </div>
                </div>

            </div>

            <!-- Sticky Mobile Cart Bar -->
            <div x-show="showStickyCart" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-brand-red/20 p-4 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.2)] md:hidden">
                <div class="flex items-center justify-between gap-4 max-w-7xl mx-auto">
                    <div class="flex flex-col">
                        <span class="font-mono text-[10px] uppercase font-bold text-brand-charcoal line-clamp-1">{{ $product->name }}</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="font-sans font-bold text-brand-red">Rs <span x-text="formatRupees(activeCondition.price)"></span></span>
                            <span class="font-mono text-[9px] uppercase text-brand-grey border border-gray-200 px-1 py-0.5" x-text="activeCondition.grade === 'new' ? 'NEW' : 'GRADE ' + activeCondition.grade.toUpperCase()"></span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                            <form method="POST" action="{{ route('cart.add') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="product_condition_id" :value="activeCondition.id">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-brand-red text-white font-mono text-[11px] uppercase font-bold tracking-wider px-5 py-3 shadow-md active:scale-95 transition-transform flex items-center space-x-1.5">
                                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="square" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>Add</span>
                                </button>
                            </form>
                        </template>
                        <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                            <button type="button" disabled class="bg-gray-200 text-gray-400 border border-gray-300 font-mono text-[11px] uppercase font-bold tracking-wider px-5 py-3 cursor-not-allowed">
                                Out of Stock
                            </button>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- Related Products Section -->
        @if($relatedProducts->count() > 0)
            <div class="mt-20 pt-12 border-t border-gray-200">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">
                        Related Devices in {{ $product->category->name }}
                    </h2>
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="font-mono text-xs uppercase font-bold text-brand-red hover:underline">
                        View Category &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $relProduct)
                        @php $firstCond = $relProduct->conditions->first(); @endphp
                        @if($firstCond)
                            <div class="group card-glow-hover relative flex flex-col justify-between border border-gray-200 bg-white p-5">
                                
                                <div class="flex items-center justify-between mb-4">
                                    <span class="border border-brand-red text-brand-red font-mono text-[10px] font-bold uppercase px-2 py-0.5">
                                        {{ strtoupper($firstCond->grade) }}
                                    </span>
                                    <span class="font-mono text-[10px] text-brand-grey uppercase">
                                        {{ $relProduct->brand }}
                                    </span>
                                </div>

                                <div class="shine-container aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-4 mb-4 border border-gray-100">
                                    <x-product-icon :categorySlug="$relProduct->category->slug ?? ''" class="h-20 w-20 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors stroke-[1.5]" />
                                </div>

                                <div>
                                    <a href="{{ route('products.show', $relProduct->slug) }}" class="font-sans font-bold text-brand-charcoal text-base group-hover:text-brand-red transition-colors line-clamp-1">
                                        {{ $relProduct->name }}
                                    </a>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-baseline justify-between">
                                    <span class="font-mono font-bold text-brand-charcoal text-base">
                                        Rs {{ number_format($firstCond->price) }}
                                    </span>
                                    <a href="{{ route('products.show', $relProduct->slug) }}" class="font-mono text-[11px] uppercase font-bold text-white bg-brand-charcoal group-hover:bg-brand-red px-3 py-1.5 transition-colors">
                                        View
                                    </a>
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    </div>

@endsection
