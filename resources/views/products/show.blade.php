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

    <!-- Product Detail Container -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start" 
             x-data="{ 
                 conditions: {{ json_encode($product->conditions) }},
                 activeIndex: 0,
                 get activeCondition() {
                     return this.conditions[this.activeIndex] || {};
                 },
                 formatRupees(val) {
                     return Number(val || 0).toLocaleString('en-US');
                 }
             }">

            <!-- Left Column: Large SVG Silhouette Phone Image Placeholder -->
            <div class="lg:col-span-6">
                <div class="aspect-square flex items-center justify-center bg-brand-offwhite border border-gray-200 p-12 relative overflow-hidden">
                    <svg class="h-64 w-64 text-brand-charcoal/20 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                        <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                        <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                    </svg>

                    <!-- Overlay Brand Tag -->
                    <div class="absolute bottom-4 left-4 font-mono text-xs font-bold uppercase tracking-wider text-brand-grey border border-gray-200 bg-white/80 backdrop-blur px-2.5 py-1">
                        {{ strtoupper($product->brand) }} • OFFICIAL DESIGN
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Details & Interactive Condition Selector -->
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
                <h1 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-brand-charcoal">
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
                                    :class="activeIndex === idx ? 'border-brand-red bg-brand-red text-white' : 'border-gray-200 bg-white text-brand-charcoal hover:border-brand-red'"
                                    class="border-2 p-3 text-center transition-all duration-150 focus:outline-none">
                                <div class="font-mono text-xs font-bold uppercase" x-text="cond.grade === 'new' ? 'NEW' : 'GRADE ' + cond.grade.toUpperCase()"></div>
                                <div class="font-mono text-[11px] mt-1" :class="activeIndex === idx ? 'text-white/90' : 'text-brand-grey'" x-text="'Rs ' + formatRupees(cond.price)"></div>
                            </button>
                        </template>
                    </div>

                    <!-- Dynamic Price Display -->
                    <div class="pt-4 flex items-baseline justify-between">
                        <div>
                            <span class="font-mono text-xs text-brand-grey uppercase block">Active Condition Price</span>
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

                        <!-- Dynamic Stock Badge -->
                        <div>
                            <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                                <span class="font-mono text-xs font-semibold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 inline-block">
                                    ✓ IN STOCK (<span x-text="activeCondition.quantity_in_stock"></span> available)
                                </span>
                            </template>
                            <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                                <span class="font-mono text-xs font-semibold uppercase bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 inline-block">
                                    ✕ OUT OF STOCK
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Add to Cart Action -->
                <div>
                    <!-- Enabled State -->
                    <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                        <form method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_condition_id" :value="activeCondition.id">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add Selected Grade to Cart</span>
                            </button>
                        </form>
                    </template>

                    <!-- Disabled State when Stock == 0 -->
                    <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                        <button type="button" disabled class="w-full bg-gray-200 text-gray-400 border border-gray-300 font-mono text-xs uppercase font-bold tracking-wider py-4 cursor-not-allowed">
                            OUT OF STOCK — CONDITION UNAVAILABLE
                        </button>
                    </template>
                </div>

                <!-- Description Block -->
                <div class="pt-6 border-t border-gray-200">
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal mb-2">Product Description</h3>
                    <p class="font-sans text-sm text-brand-grey leading-relaxed">
                        {{ $product->description ?? 'No specific description provided for this phone model.' }}
                    </p>
                </div>

                <!-- Trust Guarantees List -->
                <div class="bg-brand-offwhite border border-gray-200 p-4 space-y-2 font-mono text-xs text-brand-charcoal">
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span x-text="activeCondition.grade === 'new' ? '1-Year Official Brand Warranty' : '7-Day Check Time / Testing Guarantee for Used Devices'"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span>Full 70-Point Hardware & Battery Health Diagnostic</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red font-bold">✓</span>
                        <span>Verified IMEI & Clean Device History Guarantee</span>
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
                            <div class="group relative flex flex-col justify-between border border-gray-200 bg-white p-5 hover:border-brand-red hover:-translate-y-1 transition-all duration-200">
                                
                                <div class="flex items-center justify-between mb-4">
                                    <span class="border border-brand-red text-brand-red font-mono text-[10px] font-bold uppercase px-2 py-0.5">
                                        {{ strtoupper($firstCond->grade) }}
                                    </span>
                                    <span class="font-mono text-[10px] text-brand-grey uppercase">
                                        {{ $relProduct->brand }}
                                    </span>
                                </div>

                                <div class="aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-4 mb-4 border border-gray-100">
                                    <svg class="h-20 w-20 text-brand-charcoal/20 group-hover:text-brand-red/30 transition-colors stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                        <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                                        <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                                    </svg>
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
                                    <a href="{{ route('products.show', $relProduct->slug) }}" class="font-mono text-[11px] uppercase font-bold text-white bg-brand-charcoal group-hover:bg-brand-red px-2.5 py-1.5 transition-colors">
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
