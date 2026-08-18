@extends('layouts.storefront')

@section('title', $product->name . ' - Buy in Nepal | FoneX Store')
@section('meta_description', Str::limit(strip_tags($product->description), 150))
@section('meta_keywords', $product->brand . ', ' . $product->name . ', used ' . $product->name . ', buy phones in nepal')
@section('og_image', $product->image ? url(Storage::url($product->image)) : asset('images/default-og.jpg'))
@section('og_type', 'product')

@section('content')

    <!-- Breadcrumb Header -->
    <div class="bg-brand-offwhite border-b border-gray-200 py-4">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
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
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start" 
             x-data="{ 
                 conditions: {{ json_encode($product->conditions) }},
                 gallery: {{ json_encode($product->gallery ?? []) }},
                 activeImage: '{{ $product->image ? Storage::url($product->image) : '' }}',
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
                 },
                 isZooming: false,
                 zoomX: '50%',
                 zoomY: '50%',
                 handleZoom(e) {
                     if (window.innerWidth < 1024) return;
                     const rect = e.currentTarget.getBoundingClientRect();
                     const x = ((e.clientX - rect.left) / rect.width) * 100;
                     const y = ((e.clientY - rect.top) / rect.height) * 100;
                     this.zoomX = `${x}%`;
                     this.zoomY = `${y}%`;
                     this.isZooming = true;
                 },
                 resetZoom() {
                     this.isZooming = false;
                 }
             }"
             @scroll.window="checkSticky"
             @resize.window="checkSticky">

            <!-- Left Column: Interactive 3D Phone Presentation Stage -->
            <div class="lg:col-span-6 relative lg:sticky lg:top-28 lg:h-max z-20 space-y-4" data-aos="fade-right">
                <div class="aspect-square flex items-center justify-center bg-brand-offwhite border border-gray-200 relative overflow-hidden shadow-sm transition-opacity duration-300 cursor-zoom-in"
                     @mousemove="handleZoom"
                     @mouseleave="resetZoom">
                    
                    <!-- Ambient Red Glow Pedestal -->
                    <div class="absolute bottom-4 h-32 w-64 bg-brand-red/20 blur-3xl rounded-full"></div>

                    <!-- Levitating Phone Contour -->
                    <template x-if="activeImage">
                        <img :src="activeImage" 
                             class="w-full h-full object-cover relative z-10 pointer-events-none" 
                             :style="isZooming ? `transform: scale(2.5); transform-origin: ${zoomX} ${zoomY}; transition: transform 0.1s ease-out;` : `transform: scale(1); transform-origin: center; transition: transform 0.25s ease-out;`" />
                    </template>
                    <template x-if="!activeImage">
                        <x-product-icon :categorySlug="$product->category->slug ?? ''" class="w-full h-full p-12 text-brand-charcoal/30 stroke-[1.5] relative z-10" />
                    </template>

                    <!-- Overlay Brand Tag -->
                    <div class="absolute bottom-4 left-4 font-mono text-xs font-bold uppercase tracking-wider text-brand-grey border border-gray-200 bg-white/90 backdrop-blur px-3 py-1.5 shadow-sm">
                        {{ strtoupper($product->brand) }} • OFFICIAL DESIGN
                    </div>

                    <!-- Grade Indicator -->
                    <div class="absolute top-4 right-4 bg-brand-red text-white font-mono text-xs font-bold uppercase px-3 py-1 shadow">
                        <span x-text="activeCondition.grade === 'new' ? 'BRAND NEW' : 'GRADE ' + activeCondition.grade.toUpperCase()"></span>
                    </div>

                </div>
                
                <!-- Gallery Thumbnails -->
                <template x-if="gallery.length > 0">
                    <div class="grid grid-cols-5 gap-4">
                        <button @click="activeImage = '{{ $product->image ? Storage::url($product->image) : '' }}'" 
                                :class="activeImage === '{{ $product->image ? Storage::url($product->image) : '' }}' ? 'border-brand-red ring-1 ring-brand-red' : 'border-gray-200 hover:border-gray-300'"
                                class="aspect-square bg-brand-offwhite border flex items-center justify-center p-2 transition-all">
                            <img src="{{ $product->image ? Storage::url($product->image) : '' }}" class="object-contain h-full w-full" />
                        </button>
                        <template x-for="image in gallery" :key="image">
                            <button @click="activeImage = '{{ Storage::url('') }}' + image" 
                                    :class="activeImage === '{{ Storage::url('') }}' + image ? 'border-brand-red ring-1 ring-brand-red' : 'border-gray-200 hover:border-gray-300'"
                                    class="aspect-square bg-brand-offwhite border flex items-center justify-center p-2 transition-all">
                                <img :src="'{{ Storage::url('') }}' + image" class="object-contain h-full w-full" />
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Right Column: Product Details & Dynamic Condition Tabs -->
            <div class="lg:col-span-6 space-y-6" data-aos="fade-left">
                
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
                                <template x-if="activeCondition.original_price && Number(activeCondition.original_price) > Number(activeCondition.price)">
                                    <span class="font-mono text-sm text-brand-grey line-through">
                                        Rs <span x-text="formatRupees(activeCondition.original_price)"></span>
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

                <!-- Actions Container -->
                <div x-ref="addToCartContainer" class="flex flex-col sm:flex-row gap-4">
                    <template x-if="Number(activeCondition.quantity_in_stock) > 0">
                        <form method="POST" action="{{ route('cart.add') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="product_condition_id" :value="activeCondition.id">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full bg-brand-red text-white font-mono text-xs uppercase font-bold tracking-wider py-4 shadow-lg shadow-brand-red/20 hover:bg-brand-red-dark transition-all active:scale-95 flex items-center justify-center space-x-2">
                                <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add Selected Grade to Cart</span>
                            </button>
                        </form>
                    </template>

                    <template x-if="Number(activeCondition.quantity_in_stock) <= 0">
                        <button type="button" disabled class="flex-1 bg-gray-200 text-gray-400 border border-gray-300 font-mono text-xs uppercase font-bold tracking-wider py-4 cursor-not-allowed">
                            OUT OF STOCK — CONDITION UNAVAILABLE
                        </button>
                    </template>

                    <!-- Wishlist Toggle -->
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                        @csrf
                        @php $inWishlist = in_array($product->id, session('wishlist', [])); @endphp
                        <button type="submit" class="h-full px-6 flex items-center justify-center border {{ $inWishlist ? 'border-brand-red bg-brand-red/5' : 'border-gray-300 bg-white hover:border-brand-charcoal hover:bg-gray-50' }} transition-colors" title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                            <svg class="h-5 w-5 {{ $inWishlist ? 'text-brand-red fill-current' : 'text-brand-charcoal' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </form>
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
                 class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-brand-red/20 p-4 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.2)]">
                <div class="flex items-center justify-between gap-4 max-w-[90rem] mx-auto">
                    <div class="flex flex-col">
                        <span class="font-mono text-[10px] uppercase font-bold text-brand-charcoal line-clamp-1">{{ $product->name }}</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="font-sans font-bold text-brand-red">Rs <span x-text="formatRupees(activeCondition.price)"></span></span>
                            <template x-if="activeCondition.original_price && Number(activeCondition.original_price) > Number(activeCondition.price)">
                                <span class="font-mono text-[9px] text-brand-grey line-through">Rs <span x-text="formatRupees(activeCondition.original_price)"></span></span>
                            </template>
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
                                    @if($relProduct->image)
                                        <img src="{{ Storage::url($relProduct->image) }}" class="h-20 w-20 object-contain group-hover:scale-110 transition-transform duration-300" />
                                    @else
                                        <x-product-icon :categorySlug="$relProduct->category->slug ?? ''" class="h-20 w-20 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors stroke-[1.5]" />
                                    @endif
                                </div>

                                <div>
                                    <a href="{{ route('products.show', $relProduct->slug) }}" class="font-sans font-bold text-brand-charcoal text-base group-hover:text-brand-red transition-colors line-clamp-1">
                                        {{ $relProduct->name }}
                                    </a>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-baseline justify-between">
                                    <div class="flex items-baseline space-x-1.5">
                                        <span class="font-mono font-bold text-brand-charcoal text-base">
                                            Rs {{ number_format($firstCond->price) }}
                                        </span>
                                        @if($firstCond->original_price && $firstCond->original_price > $firstCond->price)
                                            <span class="font-mono text-[10px] text-brand-grey line-through">
                                                Rs {{ number_format($firstCond->original_price) }}
                                            </span>
                                        @endif
                                    </div>
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

        <!-- Reviews Section -->
        <div class="mt-24 border-t border-gray-200 pt-16">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Reviews List & Summary -->
                <div class="lg:w-2/3 space-y-8">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight text-brand-charcoal uppercase">Customer Reviews</h2>
                        <div class="flex items-center space-x-4 mt-2">
                            <div class="flex items-center text-brand-red">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($product->reviews_avg_rating ?? 0))
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="font-mono text-sm text-brand-grey">{{ number_format($product->reviews_avg_rating ?? 0, 1) }} out of 5 ({{ $product->reviews_count ?? 0 }} Reviews)</span>
                        </div>
                    </div>

                    @if($product->reviews->isEmpty())
                        <div class="bg-brand-offwhite p-6 border border-gray-200 font-mono text-sm text-brand-grey">
                            No reviews yet. Be the first to review this product!
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($product->reviews as $review)
                                <div class="bg-brand-offwhite p-6 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="font-bold text-brand-charcoal">{{ $review->name }}</div>
                                        <div class="font-mono text-xs text-brand-grey">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="flex items-center text-brand-red mb-4">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-sm text-brand-charcoal/80 leading-relaxed">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Submit Review Form -->
                <div class="lg:w-1/3">
                    <div class="bg-brand-charcoal text-white p-8">
                        <h3 class="text-xl font-bold uppercase tracking-wide mb-6">Write a Review</h3>
                        
                        @if(session('success'))
                            <div class="bg-brand-red/10 border border-brand-red text-brand-red p-4 mb-6 font-mono text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('products.reviews.store', $product) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block font-mono text-xs uppercase mb-1">Your Name</label>
                                <input type="text" name="name" required class="w-full bg-white/10 border-white/20 text-white focus:border-brand-red focus:ring-0">
                            </div>
                            <div>
                                <label class="block font-mono text-xs uppercase mb-1">Rating</label>
                                <select name="rating" required class="w-full bg-[#303030] border-white/20 text-white focus:border-brand-red focus:ring-0 appearance-none">
                                    <option value="5">5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Good</option>
                                    <option value="3">3 Stars - Average</option>
                                    <option value="2">2 Stars - Poor</option>
                                    <option value="1">1 Star - Terrible</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-mono text-xs uppercase mb-1">Review Comment</label>
                                <textarea name="comment" required rows="4" class="w-full bg-white/10 border-white/20 text-white focus:border-brand-red focus:ring-0"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-brand-red hover:bg-red-700 text-white font-mono text-sm font-bold uppercase py-3 transition-colors">
                                Submit Review
                            </button>
                            <p class="font-mono text-[10px] text-white/50 text-center mt-2">Note: Reviews are moderated and must be approved before they appear.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
