@extends('layouts.storefront')

@section('title', $product->name . ' — FoneX Store')

@section('content')

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $product->name,
    'image' => $product->image ? url(Storage::url($product->image)) : asset('images/default-og.jpg'),
    'description' => Str::limit(strip_tags($product->description), 150),
    'brand' => [
        '@type' => 'Brand',
        'name' => $product->brand,
    ],
    'offers' => [
        '@type' => 'AggregateOffer',
        'url' => url()->current(),
        'priceCurrency' => 'NPR',
        'lowPrice' => (float) ($product->conditions->min('price') ?? 0),
        'highPrice' => (float) ($product->conditions->max('price') ?? 0),
        'offerCount' => $product->conditions->count(),
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@php
    $galleryImages = [];
    if ($product->image) {
        $galleryImages[] = Storage::url($product->image);
    }
    if (!empty($product->gallery) && is_array($product->gallery)) {
        foreach ($product->gallery as $gal) {
            $galleryImages[] = Storage::url($gal);
        }
    }
    $galleryImages = array_values(array_unique($galleryImages));
    $initialMainImage = count($galleryImages) > 0 ? $galleryImages[0] : null;

    $conditionSpecs = [
        'new' => [
            'name' => 'Brand New Sealed',
            'tag' => 'Factory Sealed',
            'desc' => '100% Official warranty • Zero battery cycles • Sealed box with original accessories',
            'badgeColor' => 'bg-emerald-600',
        ],
        'A' => [
            'name' => 'Grade A (Pristine)',
            'tag' => 'Like-New',
            'desc' => 'Flawless display & body • Battery health 90%+ • 100-point diagnostic certified',
            'badgeColor' => 'bg-blue-600',
        ],
        'B' => [
            'name' => 'Grade B (Excellent)',
            'tag' => 'Most Popular',
            'desc' => 'Light micro-marks invisible from arm length • Battery health 85%+ • 100% functional',
            'badgeColor' => 'bg-brand-charcoal',
        ],
        'C' => [
            'name' => 'Grade C (Fair Value)',
            'tag' => 'Best Value',
            'desc' => 'Noticeable cosmetic wear • Fully tested & verified internal components • Best budget',
            'badgeColor' => 'bg-stone-600',
        ],
    ];

    $inWishlist = in_array($product->id, session('wishlist', []));
    $conditionLookup = [];
    foreach($product->conditions as $cond) {
        $gradeKey = $cond->grade instanceof \App\Enums\GradeEnum ? $cond->grade->value : $cond->grade;
        $conditionLookup[$gradeKey] = $cond;
    }
@endphp

<!-- Alpine State for the Product Detail Page -->
<div x-data="productDetail({
        conditions: {{ \Illuminate\Support\Js::from($product->conditions) }},
        mainImage: '{{ $initialMainImage }}'
    })" 
    class="bg-brand-white min-h-screen">

    <!-- Breadcrumb Strip -->
    <div class="border-b-2 border-brand-charcoal bg-brand-offwhite/60 py-3.5">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2 font-mono text-xs">
                <nav class="flex items-center space-x-2 font-bold uppercase tracking-wider text-brand-grey">
                    <a href="{{ route('home') }}" class="hover:text-brand-charcoal transition-colors">Home</a>
                    <span>/</span>
                    <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}" class="hover:text-brand-charcoal transition-colors">
                        {{ $product->category->name ?? 'Phones' }}
                    </a>
                    <span>/</span>
                    <a href="{{ route('products.index', ['brand' => $product->brand]) }}" class="hover:text-brand-charcoal transition-colors">
                        {{ $product->brand }}
                    </a>
                    <span>/</span>
                    <span class="text-brand-charcoal truncate max-w-[200px] sm:max-w-none">{{ $product->name }}</span>
                </nav>

                <div class="hidden sm:flex items-center space-x-3 text-[11px] font-bold uppercase tracking-wider text-brand-charcoal">
                    <span class="inline-flex items-center space-x-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>100-Point Inspected</span>
                    </span>
                    <span class="text-brand-grey">•</span>
                    <span class="text-brand-charcoal/80">7-Day Checking Guarantee</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Product Showcase Grid -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
            
            <!-- Left Column: Interactive Product Showcase & Gallery (7 cols) -->
            <div class="lg:col-span-7 flex flex-col space-y-6 lg:sticky lg:top-24">
                
                <!-- Main Visual Canvas -->
                <div class="relative bg-gradient-to-b from-brand-offwhite via-white to-brand-offwhite border-2 border-brand-charcoal rounded-xl p-8 sm:p-12 flex items-center justify-center min-h-[420px] sm:min-h-[520px] overflow-hidden shadow-[6px_6px_0px_0px_rgba(42,42,44,1)] group">
                    
                    <!-- Subtle background matrix pattern -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#2A2A2C 2px, transparent 2px); background-size: 28px 28px;"></div>

                    <!-- Top Left: Brand Tag -->
                    <div class="absolute top-4 left-4 z-10 flex items-center space-x-2">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white border border-brand-charcoal px-3 py-1 shadow-[2px_2px_0px_0px_rgba(42,42,44,1)]">
                            {{ $product->brand }}
                        </span>
                        <template x-if="selectedCondition">
                            <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-white bg-brand-charcoal px-2.5 py-1" x-text="selectedCondition.grade.toUpperCase() === 'NEW' ? 'SEALED BOX' : 'GRADE ' + selectedCondition.grade.toUpperCase()"></span>
                        </template>
                    </div>

                    <!-- Top Right: Stock Status Pill -->
                    <div class="absolute top-4 right-4 z-10">
                        <template x-if="selectedCondition && selectedCondition.quantity_in_stock > 0">
                            <span class="inline-flex items-center space-x-1.5 font-mono text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 border border-emerald-300 px-3 py-1 rounded-full shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span x-text="selectedCondition.quantity_in_stock + ' In Stock'"></span>
                            </span>
                        </template>
                        <template x-if="selectedCondition && selectedCondition.quantity_in_stock <= 0">
                            <span class="inline-flex items-center space-x-1.5 font-mono text-xs font-bold uppercase tracking-wider text-rose-800 bg-rose-100 border border-rose-300 px-3 py-1 rounded-full">
                                <span>Sold Out</span>
                            </span>
                        </template>
                    </div>

                    <!-- Main Display Image / Icon -->
                    <div class="relative z-0 w-full flex items-center justify-center py-4">
                        <template x-if="activeImage">
                            <img :src="activeImage" 
                                 alt="{{ $product->name }}" 
                                 class="max-h-[360px] sm:max-h-[460px] w-auto object-contain drop-shadow-2xl transition-all duration-500 ease-out group-hover:scale-105" />
                        </template>
                        <template x-if="!activeImage">
                            <div class="py-12 flex flex-col items-center justify-center text-brand-charcoal/40">
                                <x-product-icon :categorySlug="$product->category->slug ?? ''" class="h-44 sm:h-56 stroke-[1.2] drop-shadow-md group-hover:scale-105 transition-transform duration-300" />
                                <span class="font-mono text-xs uppercase font-bold tracking-widest mt-4">Verified Inspection Unit</span>
                            </div>
                        </template>
                    </div>

                    <!-- Bottom Right: Live Savings Pill -->
                    <template x-if="savingsPercent > 0">
                        <div class="absolute bottom-4 right-4 z-10 bg-brand-red text-white font-mono text-xs font-bold uppercase tracking-wider px-3 py-1.5 shadow-[3px_3px_0px_0px_rgba(42,42,44,1)]">
                            <span x-text="'SAVE ' + savingsPercent + '%'"></span>
                        </div>
                    </template>
                </div>

                <!-- Interactive Multi-Angle Gallery Thumbnails (if available) -->
                @if(count($galleryImages) > 1)
                    <div class="flex items-center space-x-3 overflow-x-auto pb-2 scrollbar-thin">
                        @foreach($galleryImages as $index => $img)
                            <button type="button" 
                                    @click="activeImage = '{{ $img }}'" 
                                    :class="activeImage === '{{ $img }}' ? 'border-brand-red ring-2 ring-brand-red/30 shadow-[3px_3px_0px_0px_rgba(200,32,45,1)]' : 'border-brand-charcoal/30 hover:border-brand-charcoal bg-brand-offwhite'"
                                    class="h-20 w-20 sm:h-24 sm:w-24 rounded-lg border-2 bg-white p-2 flex-shrink-0 flex items-center justify-center transition-all duration-200">
                                <img src="{{ $img }}" alt="{{ $product->name }} thumb {{ $index + 1 }}" class="max-h-full max-w-full object-contain" />
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Inspection & Guarantee Trust Highlights (Bento Grid) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="border-2 border-brand-charcoal/20 bg-brand-offwhite/50 rounded-lg p-3 text-center">
                        <div class="font-mono text-brand-red text-base mb-0.5">✓ 100-PT</div>
                        <div class="font-sans text-[11px] font-bold text-brand-charcoal uppercase tracking-wider">Hardware Tested</div>
                        <div class="font-sans text-[10px] text-brand-grey">Sensors, Audio, Cameras</div>
                    </div>
                    <div class="border-2 border-brand-charcoal/20 bg-brand-offwhite/50 rounded-lg p-3 text-center">
                        <div class="font-mono text-brand-red text-base mb-0.5">🛡️ 7-DAY</div>
                        <div class="font-sans text-[11px] font-bold text-brand-charcoal uppercase tracking-wider">Check Guarantee</div>
                        <div class="font-sans text-[10px] text-brand-grey">Hassle-free replacement</div>
                    </div>
                    <div class="border-2 border-brand-charcoal/20 bg-brand-offwhite/50 rounded-lg p-3 text-center">
                        <div class="font-mono text-brand-red text-base mb-0.5">🔋 85%+</div>
                        <div class="font-sans text-[11px] font-bold text-brand-charcoal uppercase tracking-wider">Battery Health</div>
                        <div class="font-sans text-[10px] text-brand-grey">Verified peak capacity</div>
                    </div>
                    <div class="border-2 border-brand-charcoal/20 bg-brand-offwhite/50 rounded-lg p-3 text-center">
                        <div class="font-mono text-brand-red text-base mb-0.5">🚚 FAST</div>
                        <div class="font-sans text-[11px] font-bold text-brand-charcoal uppercase tracking-wider">Nepal Delivery</div>
                        <div class="font-sans text-[10px] text-brand-grey">Doorstep COD available</div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Product Configurator & Purchasing Flow (5 cols) -->
            <div class="lg:col-span-5 flex flex-col space-y-7">
                
                <!-- Title & Reviews Heading Area -->
                <div class="border-b-2 border-brand-charcoal pb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand-grey">{{ $product->category->name ?? 'Smartphones' }}</span>
                            <span class="text-brand-grey">•</span>
                            <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal">{{ $product->brand }}</span>
                        </div>

                        <!-- Wishlist Toggle Form -->
                        <form action="{{ route('wishlist.toggle', $product->slug) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    aria-label="Toggle Wishlist" 
                                    class="p-2 rounded-lg border border-brand-charcoal/20 hover:border-brand-red hover:bg-brand-red/5 text-brand-charcoal transition-colors">
                                <svg class="h-5 w-5 {{ $inWishlist ? 'text-brand-red fill-brand-red' : 'text-brand-charcoal hover:text-brand-red' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl uppercase tracking-tight text-brand-charcoal leading-[1.0] mb-3">
                        {{ $product->name }}
                    </h1>

                    <!-- Rating summary with interactive jump -->
                    <div class="flex items-center space-x-3 mt-3">
                        <div class="flex items-center text-amber-500">
                            @php $rating = round($product->reviews_avg_rating ?? 5); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $rating ? 'fill-current text-amber-400' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="font-mono text-xs font-bold text-brand-charcoal">
                            {{ number_format($product->reviews_avg_rating ?? 5.0, 1) }}
                        </span>
                        <span class="text-brand-grey text-xs">•</span>
                        <button type="button" @click="activeTab = 'reviews'; document.getElementById('details-section').scrollIntoView({behavior: 'smooth'})" class="font-mono text-xs font-bold text-brand-grey hover:text-brand-red underline uppercase tracking-wider transition-colors">
                            {{ $product->reviews_count }} Verified Reviews
                        </button>
                    </div>
                </div>

                <!-- Dynamic Live Price Card -->
                <div class="bg-brand-offwhite border-2 border-brand-charcoal rounded-xl p-5 sm:p-6 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                    <div class="font-mono text-xs font-bold uppercase tracking-wider text-brand-grey mb-1.5">
                        Selected Device Price (NPR)
                    </div>
                    
                    <div class="flex items-baseline flex-wrap gap-x-4 gap-y-2">
                        <template x-if="selectedCondition">
                            <span class="font-mono text-4xl sm:text-5xl font-black text-brand-red tracking-tight" x-text="'Rs ' + Number(selectedCondition.price).toLocaleString()"></span>
                        </template>
                        <template x-if="!selectedCondition">
                            <span class="font-mono text-4xl font-bold text-brand-grey">Out of Stock</span>
                        </template>

                        <template x-if="savingsAmount > 0">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-lg text-brand-grey line-through font-bold" x-text="'Rs ' + Number(selectedCondition.original_price).toLocaleString()"></span>
                                <span class="font-mono text-xs font-bold uppercase tracking-wider text-white bg-brand-charcoal px-2.5 py-1 rounded-sm shadow-sm" x-text="'Save Rs ' + Number(savingsAmount).toLocaleString()"></span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3 pt-3 border-t border-brand-charcoal/10 flex items-center justify-between text-xs font-mono text-brand-charcoal/70">
                        <span>Includes official VAT bill & test report</span>
                        <span class="text-emerald-700 font-bold">● Pay on Delivery (COD)</span>
                    </div>
                </div>

                <!-- Condition Selector (Tactile Bento Grid) -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">
                            1. Select Device Condition
                        </span>
                        <button type="button" @click="activeTab = 'grading'; document.getElementById('details-section').scrollIntoView({behavior: 'smooth'})" class="font-mono text-xs uppercase font-bold text-brand-grey hover:text-brand-red underline decoration-brand-grey underline-offset-4 transition-colors">
                            Condition Guide &rarr;
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <template x-for="c in conditions" :key="c.id">
                            <button type="button" 
                                    @click="selectCondition(c)" 
                                    :aria-pressed="selectedCondition && selectedCondition.id === c.id"
                                    :class="{
                                        'border-brand-red bg-brand-white ring-2 ring-brand-red/20 shadow-[4px_4px_0px_0px_rgba(200,32,45,1)]': selectedCondition && selectedCondition.id === c.id,
                                        'border-brand-charcoal/30 bg-brand-offwhite/40 hover:bg-white hover:border-brand-charcoal': !selectedCondition || selectedCondition.id !== c.id,
                                        'opacity-40 cursor-not-allowed': c.quantity_in_stock <= 0
                                    }"
                                    class="text-left border-2 rounded-lg p-3.5 sm:p-4 transition-all duration-200 relative group/chip">
                                
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-display text-base sm:text-lg uppercase tracking-tight text-brand-charcoal font-bold" 
                                                  x-text="c.grade.toUpperCase() === 'NEW' ? 'Brand New Sealed' : 'Grade ' + c.grade.toUpperCase() + ' Certified'"></span>
                                            
                                            <span class="font-mono text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm"
                                                  :class="c.grade.toUpperCase() === 'NEW' ? 'bg-emerald-100 text-emerald-800' : (c.grade.toUpperCase() === 'A' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-brand-charcoal')"
                                                  x-text="c.grade.toUpperCase() === 'NEW' ? 'Factory Sealed' : (c.grade.toUpperCase() === 'A' ? 'Pristine' : (c.grade.toUpperCase() === 'B' ? 'Popular' : 'Best Value'))">
                                            </span>
                                        </div>

                                        <p class="font-sans text-xs text-brand-grey mt-1 font-light leading-snug"
                                           x-text="c.grade.toUpperCase() === 'NEW' ? 'Official distributor sealed box • Complete accessories • 1-Year warranty' : (c.grade.toUpperCase() === 'A' ? 'Near-flawless display & body • 90%+ battery capacity • 100-pt certified' : (c.grade.toUpperCase() === 'B' ? 'Light micro-marks not visible from 20cm • 85%+ battery • 100% functional' : 'Noticeable exterior cosmetic wear • Core internals 100% verified'))">
                                        </p>
                                    </div>

                                    <!-- Price & Stock Column -->
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-mono font-bold text-sm sm:text-base text-brand-charcoal group-hover/chip:text-brand-red transition-colors" x-text="'Rs ' + Number(c.price).toLocaleString()"></div>
                                        <div class="font-mono text-[11px] font-bold uppercase mt-1 tracking-wider" 
                                             :class="c.quantity_in_stock > 0 ? 'text-emerald-700' : 'text-rose-600'"
                                             x-text="c.quantity_in_stock > 0 ? c.quantity_in_stock + ' IN STOCK' : 'OUT OF STOCK'">
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Add to Cart & Purchasing Actions -->
                <div class="border-t-2 border-brand-charcoal pt-6">
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_condition_id" :value="selectedCondition?.id">
                        <input type="hidden" name="quantity" :value="quantity">

                        <div class="flex items-center space-x-3 mb-4">
                            <!-- Tactile Quantity Stepper -->
                            <div class="flex items-center border-2 border-brand-charcoal rounded-lg bg-brand-white shadow-[2px_2px_0px_0px_rgba(42,42,44,1)] overflow-hidden h-14">
                                <button type="button" 
                                        @click="decrementQty()" 
                                        :disabled="quantity <= 1"
                                        aria-label="Decrease quantity" 
                                        class="h-full px-3.5 font-mono text-base font-bold text-brand-charcoal hover:bg-brand-offwhite disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                    −
                                </button>
                                <span class="w-10 text-center font-mono text-sm font-bold text-brand-charcoal" x-text="quantity"></span>
                                <button type="button" 
                                        @click="incrementQty()" 
                                        :disabled="!selectedCondition || quantity >= selectedCondition.quantity_in_stock"
                                        aria-label="Increase quantity" 
                                        class="h-full px-3.5 font-mono text-base font-bold text-brand-charcoal hover:bg-brand-offwhite disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                    +
                                </button>
                            </div>

                            <!-- Primary Add to Cart Button -->
                            <button type="submit" 
                                    :disabled="!selectedCondition || selectedCondition.quantity_in_stock <= 0"
                                    class="flex-grow h-14 rounded-lg bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm uppercase font-bold tracking-widest shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-x-0.5 hover:translate-y-0.5 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center space-x-3">
                                <svg class="h-5 w-5 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span x-text="selectedCondition && selectedCondition.quantity_in_stock > 0 ? 'Add to Cart • Instant Dispatch' : 'Condition Unavailable'"></span>
                                <span class="font-bold">&rarr;</span>
                            </button>
                        </div>
                    </form>

                    <!-- Trade-In Value Accelerator -->
                    <a href="{{ route('trade-in.create') }}" class="group w-full flex items-center justify-between border-2 border-brand-charcoal/30 hover:border-brand-charcoal bg-brand-offwhite/50 hover:bg-brand-white rounded-lg p-3.5 transition-all duration-200 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-brand-red/10 text-brand-red font-bold text-lg group-hover:scale-110 transition-transform">🔄</span>
                            <div>
                                <div class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal group-hover:text-brand-red transition-colors">
                                    Have an old phone? Trade-In & Upgrade
                                </div>
                                <div class="font-sans text-[11px] text-brand-grey font-light">
                                    Exchange old iPhone, Samsung, or Xiaomi for instant store credit towards this device
                                </div>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-bold text-brand-charcoal group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <!-- Local Delivery Assurance -->
                <div class="bg-brand-offwhite/60 border border-brand-charcoal/15 rounded-lg p-4 font-mono text-xs text-brand-charcoal/80 space-y-2">
                    <div class="flex items-center space-x-2 text-emerald-800 font-bold">
                        <span>⚡ Kathmandu Express:</span>
                        <span class="font-normal">Order before 3:00 PM for same-day delivery</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>📍 Store Pickup:</span>
                        <span class="font-normal">Fone X Store Kathmandu (Ready in 30 mins)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>🛡️ Return Guarantee:</span>
                        <span class="font-normal">7 days full checking window on every unit</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Deep-Dive Details, Inspection & Reviews Tabs Section -->
    <div id="details-section" class="border-t-2 border-brand-charcoal bg-brand-offwhite/40 py-16 lg:py-24">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            
            <div class="max-w-5xl mx-auto">
                <!-- Interactive Tab Navigation -->
                <div class="flex items-center space-x-2 sm:space-x-4 border-b-2 border-brand-charcoal pb-px overflow-x-auto scrollbar-thin">
                    <button type="button" 
                            @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'border-b-4 border-brand-red text-brand-charcoal font-black bg-brand-white' : 'text-brand-grey hover:text-brand-charcoal font-bold'"
                            class="py-3 px-4 sm:px-6 font-mono text-xs sm:text-sm uppercase tracking-wider transition-colors whitespace-nowrap">
                        1. Device Overview
                    </button>
                    <button type="button" 
                            @click="activeTab = 'inspection'" 
                            :class="activeTab === 'inspection' ? 'border-b-4 border-brand-red text-brand-charcoal font-black bg-brand-white' : 'text-brand-grey hover:text-brand-charcoal font-bold'"
                            class="py-3 px-4 sm:px-6 font-mono text-xs sm:text-sm uppercase tracking-wider transition-colors whitespace-nowrap flex items-center space-x-1.5">
                        <span>2. 100-Point Inspection</span>
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    </button>
                    <button type="button" 
                            @click="activeTab = 'grading'" 
                            :class="activeTab === 'grading' ? 'border-b-4 border-brand-red text-brand-charcoal font-black bg-brand-white' : 'text-brand-grey hover:text-brand-charcoal font-bold'"
                            class="py-3 px-4 sm:px-6 font-mono text-xs sm:text-sm uppercase tracking-wider transition-colors whitespace-nowrap">
                        3. Condition Standards
                    </button>
                    <button type="button" 
                            @click="activeTab = 'reviews'" 
                            :class="activeTab === 'reviews' ? 'border-b-4 border-brand-red text-brand-charcoal font-black bg-brand-white' : 'text-brand-grey hover:text-brand-charcoal font-bold'"
                            class="py-3 px-4 sm:px-6 font-mono text-xs sm:text-sm uppercase tracking-wider transition-colors whitespace-nowrap flex items-center space-x-2">
                        <span>4. Customer Reviews</span>
                        <span class="font-mono text-xs bg-brand-charcoal text-white px-2 py-0.5 rounded-full">{{ $product->reviews_count }}</span>
                    </button>
                </div>

                <!-- Tab Content Containers -->
                <div class="mt-8 bg-brand-white border-2 border-brand-charcoal rounded-xl p-6 sm:p-10 shadow-[6px_6px_0px_0px_rgba(42,42,44,1)]">
                    
                    <!-- TAB 1: Device Overview & Description -->
                    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-4">
                            About {{ $product->name }}
                        </h2>
                        
                        <div class="prose prose-base sm:prose-lg max-w-none text-brand-charcoal/90 font-sans leading-relaxed">
                            {!! $product->description !!}
                        </div>

                        <!-- Technical Specs Quick Snapshot -->
                        <div class="mt-8 pt-8 border-t border-brand-charcoal/15">
                            <h3 class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red mb-4">Device Snapshot</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 font-mono text-xs">
                                <div class="bg-brand-offwhite p-3 rounded border border-brand-charcoal/10">
                                    <span class="text-brand-grey block text-[10px] uppercase">Manufacturer</span>
                                    <span class="font-bold text-brand-charcoal">{{ $product->brand }}</span>
                                </div>
                                <div class="bg-brand-offwhite p-3 rounded border border-brand-charcoal/10">
                                    <span class="text-brand-grey block text-[10px] uppercase">Product Line</span>
                                    <span class="font-bold text-brand-charcoal">{{ $product->category->name ?? 'Smartphone' }}</span>
                                </div>
                                <div class="bg-brand-offwhite p-3 rounded border border-brand-charcoal/10">
                                    <span class="text-brand-grey block text-[10px] uppercase">IMEI Status</span>
                                    <span class="font-bold text-emerald-700">✓ Clean / NTA Registered</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: 100-Point Inspection Details -->
                    <div x-show="activeTab === 'inspection'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-brand-charcoal/15 mb-6 gap-3">
                            <div>
                                <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">
                                    FoneX 100-Point Diagnostic Testing
                                </h2>
                                <p class="font-sans text-xs text-brand-grey mt-1">Every pre-owned phone undergoes rigorous hardware & software validation prior to inventory listing.</p>
                            </div>
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-emerald-800 bg-emerald-100 border border-emerald-300 px-3 py-1.5 rounded-sm flex-shrink-0">
                                ✓ Certified Pass
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-mono text-xs">
                            <div class="border border-brand-charcoal/20 rounded-lg p-4 bg-brand-offwhite/30">
                                <h3 class="font-bold text-brand-charcoal text-sm uppercase flex items-center space-x-2 mb-3">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Display & Touch Matrix</span>
                                </h3>
                                <ul class="space-y-1.5 text-brand-charcoal/80">
                                    <li>• Multi-touch digitizer responsiveness</li>
                                    <li>• Dead pixel & burn-in inspection</li>
                                    <li>• TrueTone & Auto-Brightness calibration</li>
                                    <li>• Refresh rate stability testing</li>
                                </ul>
                            </div>

                            <div class="border border-brand-charcoal/20 rounded-lg p-4 bg-brand-offwhite/30">
                                <h3 class="font-bold text-brand-charcoal text-sm uppercase flex items-center space-x-2 mb-3">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Battery & Power Architecture</span>
                                </h3>
                                <ul class="space-y-1.5 text-brand-charcoal/80">
                                    <li>• Minimum 85%+ verified health retention</li>
                                    <li>• Fast charging circuit thermal check</li>
                                    <li>• Wireless Qi/MagSafe induction validation</li>
                                    <li>• Standby battery leakage diagnostic</li>
                                </ul>
                            </div>

                            <div class="border border-brand-charcoal/20 rounded-lg p-4 bg-brand-offwhite/30">
                                <h3 class="font-bold text-brand-charcoal text-sm uppercase flex items-center space-x-2 mb-3">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Camera Modules & Lenses</span>
                                </h3>
                                <ul class="space-y-1.5 text-brand-charcoal/80">
                                    <li>• Optical Image Stabilization (OIS) check</li>
                                    <li>• Ultra-wide, Wide & Telephoto zoom focus</li>
                                    <li>• 4K Video recording & microphone sync</li>
                                    <li>• Front selfie camera & Portrait depth mapping</li>
                                </ul>
                            </div>

                            <div class="border border-brand-charcoal/20 rounded-lg p-4 bg-brand-offwhite/30">
                                <h3 class="font-bold text-brand-charcoal text-sm uppercase flex items-center space-x-2 mb-3">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Connectivity & Security</span>
                                </h3>
                                <ul class="space-y-1.5 text-brand-charcoal/80">
                                    <li>• Face ID / Touch ID sensor calibration</li>
                                    <li>• Nepal 5G / 4G LTE signal reception on all bands</li>
                                    <li>• Dual SIM / eSIM provisioning capability</li>
                                    <li>• NTA registration & clean IMEI guarantee</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Condition Standards Guide -->
                    <div x-show="activeTab === 'grading'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-2">
                            How We Grade Our Devices
                        </h2>
                        <p class="font-sans text-xs text-brand-grey mb-6">Transparency is our core standard. Know exactly what condition you are receiving.</p>

                        <div class="space-y-4">
                            <div class="border-2 border-emerald-500/40 bg-emerald-50/40 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-display text-lg uppercase text-emerald-950 font-bold">Brand New Sealed</span>
                                    <span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">100% Brand New</span>
                                </div>
                                <p class="font-sans text-xs text-emerald-900 leading-relaxed">
                                    Direct import factory-sealed device in original packaging. 0 battery cycles, official manufacturer warranty, and all original accessories included.
                                </p>
                            </div>

                            <div class="border-2 border-blue-500/40 bg-blue-50/40 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-display text-lg uppercase text-blue-950 font-bold">Grade A — Pristine Condition</span>
                                    <span class="font-mono text-xs font-bold text-blue-800 bg-blue-100 px-2 py-0.5 rounded">Like New</span>
                                </div>
                                <p class="font-sans text-xs text-blue-900 leading-relaxed">
                                    Looks and performs almost indistinguishable from new. Screen is flawless with zero scratches. Body has virtually no marks. 100-point inspected with minimum 90% battery health.
                                </p>
                            </div>

                            <div class="border-2 border-brand-charcoal/30 bg-brand-offwhite rounded-lg p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-display text-lg uppercase text-brand-charcoal font-bold">Grade B — Excellent Value</span>
                                    <span class="font-mono text-xs font-bold text-brand-charcoal bg-white border border-brand-charcoal px-2 py-0.5 rounded">Most Popular</span>
                                </div>
                                <p class="font-sans text-xs text-brand-charcoal/80 leading-relaxed">
                                    Minor micro-scratches on casing or bezel that are invisible from normal viewing distance (20cm). Screen is clear when turned on. 100% functionally perfect with 85%+ battery health.
                                </p>
                            </div>

                            <div class="border-2 border-stone-400/40 bg-stone-50/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-display text-lg uppercase text-stone-900 font-bold">Grade C — Fair Condition</span>
                                    <span class="font-mono text-xs font-bold text-stone-800 bg-stone-200 px-2 py-0.5 rounded">Best Price</span>
                                </div>
                                <p class="font-sans text-xs text-stone-800 leading-relaxed">
                                    Noticeable exterior scratches, scuffs, or minor dings. Perfect for buyers who use a protective case and want the best possible price without sacrificing any internal functionality.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Customer Reviews & Submission -->
                    <div x-show="activeTab === 'reviews'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Left: Review Listing (7 cols) -->
                            <div class="lg:col-span-7">
                                <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-4">
                                    Customer Feedback ({{ $product->reviews_count }})
                                </h2>

                                @if($product->reviews->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($product->reviews as $rev)
                                            <div class="border-b border-brand-charcoal/15 pb-4 last:border-b-0">
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-mono text-xs font-bold text-brand-charcoal uppercase">{{ $rev->name }}</span>
                                                        <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">✓ Verified Buyer</span>
                                                    </div>
                                                    <span class="font-mono text-[11px] text-brand-grey">{{ $rev->created_at->format('M d, Y') }}</span>
                                                </div>

                                                <div class="flex items-center text-amber-500 mb-2">
                                                    @for($s = 1; $s <= 5; $s++)
                                                        <svg class="w-3.5 h-3.5 {{ $s <= $rev->rating ? 'fill-current text-amber-400' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>

                                                <p class="font-sans text-xs text-brand-charcoal/80 leading-relaxed font-light">
                                                    {{ $rev->comment }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-brand-offwhite rounded-lg p-6 text-center text-brand-grey font-mono text-xs">
                                        <p class="font-bold text-brand-charcoal mb-1">No reviews published yet</p>
                                        <p>Be the first customer in Nepal to review this device!</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Submit Review Form (5 cols) -->
                            <div class="lg:col-span-5 bg-brand-offwhite border-2 border-brand-charcoal rounded-xl p-5 sm:p-6 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                                <h3 class="font-display text-lg uppercase tracking-tight text-brand-charcoal mb-1">
                                    Write a Customer Review
                                </h3>
                                <p class="font-sans text-xs text-brand-grey mb-4">Share your feedback to help fellow buyers in Kathmandu & across Nepal.</p>

                                <form action="{{ route('products.reviews.store', $product) }}" method="POST" class="space-y-3 font-mono text-xs">
                                    @csrf

                                    {{-- Honeypot field for bot protection --}}
                                    <div style="display:none;" aria-hidden="true">
                                        <input type="text" name="author_notes" value="" autocomplete="off" tabindex="-1">
                                    </div>
                                    <div>
                                        <label for="review_name" class="block font-bold uppercase mb-1 text-brand-charcoal">Your Full Name</label>
                                        <input type="text" id="review_name" name="name" required placeholder="e.g. Ramesh Shrestha" class="w-full bg-white border-2 border-brand-charcoal px-3 py-2 text-xs font-sans focus:border-brand-red focus:ring-0 outline-none rounded-none" />
                                    </div>

                                    <div>
                                        <label for="review_rating" class="block font-bold uppercase mb-1 text-brand-charcoal">Star Rating</label>
                                        <select id="review_rating" name="rating" required class="w-full bg-white border-2 border-brand-charcoal px-3 py-2 text-xs font-mono focus:border-brand-red focus:ring-0 outline-none rounded-none">
                                            <option value="5">★★★★★ (5/5) — Outstanding Quality</option>
                                            <option value="4">★★★★☆ (4/5) — Great Device</option>
                                            <option value="3">★★★☆☆ (3/5) — Average / Fair</option>
                                            <option value="2">★★☆☆☆ (2/5) — Below Expectations</option>
                                            <option value="1">★☆☆☆☆ (1/5) — Poor</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="review_comment" class="block font-bold uppercase mb-1 text-brand-charcoal">Your Review Experience</label>
                                        <textarea id="review_comment" name="comment" rows="3" required placeholder="Mention battery life, cosmetic condition, delivery speed..." class="w-full bg-white border-2 border-brand-charcoal p-3 text-xs font-sans focus:border-brand-red focus:ring-0 outline-none rounded-none"></textarea>
                                    </div>

                                    <button type="submit" class="w-full bg-brand-charcoal hover:bg-brand-red text-white font-mono text-xs uppercase font-bold tracking-widest py-3 transition-colors duration-200 shadow-[3px_3px_0px_0px_rgba(200,32,45,1)]">
                                        Submit Verified Review
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Related Products Section -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div class="border-t-2 border-brand-charcoal bg-brand-white py-16">
            <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-8 pb-3 border-b-2 border-brand-charcoal">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red block mb-1">Explore Alternatives</span>
                        <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal">
                            Similar {{ $product->category->name ?? 'Devices' }}
                        </h2>
                    </div>
                    <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal hover:text-brand-red transition-colors flex items-center space-x-1">
                        <span>View Category</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $rel)
                        @if($rel->conditions->count() > 0)
                            <x-product-card :condition="$rel->conditions->first()" />
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>

<!-- Alpine Initialization Script -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productDetail', (initialData) => ({
            conditions: initialData.conditions,
            selectedCondition: null,
            quantity: 1,
            activeTab: 'overview',
            activeImage: initialData.mainImage,

            init() {
                // Select first in-stock condition by default, or the very first condition
                let inStock = this.conditions.find(c => c.quantity_in_stock > 0);
                this.selectedCondition = inStock ? inStock : (this.conditions[0] || null);
            },

            selectCondition(c) {
                this.selectedCondition = c;
                if (this.quantity > c.quantity_in_stock && c.quantity_in_stock > 0) {
                    this.quantity = c.quantity_in_stock;
                }
            },

            incrementQty() {
                if (this.selectedCondition && this.quantity < this.selectedCondition.quantity_in_stock) {
                    this.quantity++;
                }
            },

            decrementQty() {
                if (this.quantity > 1) {
                    this.quantity--;
                }
            },

            get savingsAmount() {
                if (!this.selectedCondition || !this.selectedCondition.original_price || Number(this.selectedCondition.original_price) <= Number(this.selectedCondition.price)) {
                    return 0;
                }
                return Number(this.selectedCondition.original_price) - Number(this.selectedCondition.price);
            },

            get savingsPercent() {
                if (!this.selectedCondition || !this.selectedCondition.original_price || Number(this.selectedCondition.original_price) <= Number(this.selectedCondition.price)) {
                    return 0;
                }
                return Math.round(((Number(this.selectedCondition.original_price) - Number(this.selectedCondition.price)) / Number(this.selectedCondition.original_price)) * 100);
            }
        }));
    });
</script>
@endsection
