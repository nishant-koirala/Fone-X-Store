@extends('layouts.storefront')

@section('title', 'FoneX Store — Every phone. Every condition. One store.')

@section('content')

    <!-- Hero Section with Solid Brand Red Background -->
    <section class="relative bg-brand-red text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-16 pb-20 sm:pt-20 sm:pb-24">
            <div class="max-w-3xl">
                <!-- Eyebrow Tagline -->
                <div class="inline-flex items-center space-x-2 border border-white/30 bg-white/10 px-3 py-1 font-mono text-xs uppercase tracking-widest text-white/90 mb-6">
                    <span class="h-2 w-2 bg-white animate-pulse"></span>
                    <span>KATHMANDU • FONE-X CERTIFIED STORE</span>
                </div>

                <!-- Hero Headline -->
                <h1 class="font-display text-4xl sm:text-6xl lg:text-7xl uppercase tracking-tight leading-[0.95] text-white">
                    Every phone.<br>
                    Every condition.<br>
                    One store.
                </h1>

                <!-- Supporting Copy -->
                <p class="mt-6 font-sans text-base sm:text-lg text-white/90 max-w-xl font-normal leading-relaxed">
                    Buy brand-new smartphones with official warranty or certified pre-owned devices graded A/B/C. Trade in your current phone for instant valuation.
                </p>
            </div>
        </div>

        <!-- Three-Path Selector directly below Hero on Red Background with White/20 Opacity Borders -->
        <div class="border-t border-white/20 bg-brand-red">
            <div class="mx-auto max-w-7xl grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-white/20">
                
                <!-- Path 01: New Phones -->
                <a href="#new-phones" class="group block p-6 sm:p-8 hover:bg-brand-red-dark/60 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-white/70">01 / DIRECT DEALS</span>
                        <svg class="h-5 w-5 text-white transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-2xl uppercase tracking-tight text-white">
                        New Phones
                    </h3>
                    <p class="mt-1 font-sans text-sm text-white/80 font-normal">
                        Factory sealed devices with official manufacturer warranty.
                    </p>
                </a>

                <!-- Path 02: Certified Used -->
                <a href="#used-phones" class="group block p-6 sm:p-8 hover:bg-brand-red-dark/60 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-white/70">02 / GRADED INVENTORY</span>
                        <svg class="h-5 w-5 text-white transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-2xl uppercase tracking-tight text-white">
                        Certified Used
                    </h3>
                    <p class="mt-1 font-sans text-sm text-white/80 font-normal">
                        Rigorously tested Grade A, B & C pre-owned phones.
                    </p>
                </a>

                <!-- Path 03: Trade & Upgrade -->
                <a href="#trade-in" class="group block p-6 sm:p-8 hover:bg-brand-red-dark/60 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-white/70">03 / INSTANT EXCHANGE</span>
                        <svg class="h-5 w-5 text-white transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-2xl uppercase tracking-tight text-white">
                        Trade & Upgrade
                    </h3>
                    <p class="mt-1 font-sans text-sm text-white/80 font-normal">
                        Bring your old device for instant credit toward your next phone.
                    </p>
                </a>

            </div>
        </div>
    </section>

    <!-- Trust Bar Strip -->
    <div class="bg-brand-offwhite border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3.5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center font-mono text-xs font-semibold text-brand-charcoal uppercase tracking-wider">
                <div class="flex items-center justify-center space-x-1.5">
                    <span class="text-brand-red font-bold">✓</span>
                    <span>70-POINT DIAGNOSTIC TEST</span>
                </div>
                <div class="flex items-center justify-center space-x-1.5">
                    <span class="text-brand-red font-bold">✓</span>
                    <span>12-MONTH WARRANTY</span>
                </div>
                <div class="flex items-center justify-center space-x-1.5">
                    <span class="text-brand-red font-bold">✓</span>
                    <span>FAST NEPALWIDE DELIVERY</span>
                </div>
                <div class="flex items-center justify-center space-x-1.5">
                    <span class="text-brand-red font-bold">✓</span>
                    <span>EASY EMI INSTALMENTS</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products Section -->
    <section id="used-phones" class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between pb-8 border-b border-gray-200 mb-10 gap-4">
                <div>
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">LIVE INVENTORY LISTINGS</span>
                    <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal mt-1">
                        Featured Products
                    </h2>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-semibold text-brand-grey uppercase">Showing</span>
                    <span class="font-mono text-xs font-bold text-brand-red bg-brand-red/10 px-2.5 py-1 border border-brand-red/20">
                        {{ $featuredConditions->count() }} RECENT ITEMS
                    </span>
                </div>
            </div>

            <!-- Product Grid (4 Columns on Desktop, 1 Column on Mobile) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @forelse($featuredConditions as $condition)
                    <div class="group relative flex flex-col justify-between border border-gray-200 bg-white p-5 hover:border-brand-red hover:-translate-y-1 transition-all duration-200">
                        
                        <!-- Top Tag Header -->
                        <div class="flex items-center justify-between mb-4">
                            <!-- Condition Grade Tag -->
                            @if(strtolower($condition->grade) === 'new')
                                <span class="border-2 border-brand-red text-brand-red font-mono text-[11px] font-bold uppercase tracking-wider px-2 py-0.5">
                                    NEW
                                </span>
                            @else
                                <span class="bg-brand-red text-white font-mono text-[11px] font-bold uppercase tracking-wider px-2 py-0.5">
                                    GRADE {{ strtoupper($condition->grade) }}
                                </span>
                            @endif

                            <!-- Stock Status Tag -->
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

                        <!-- Product Image Placeholder: SVG Silhouette Contour -->
                        <div class="aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-6 relative overflow-hidden mb-5 border border-gray-100">
                            <!-- SVG Phone Outline contour in brand-charcoal at low opacity -->
                            <svg class="h-28 w-28 text-brand-charcoal/20 group-hover:text-brand-red/30 transition-colors duration-200 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                                <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                            </svg>

                            <span class="absolute bottom-2 left-2 font-mono text-[10px] uppercase tracking-wider text-brand-grey">
                                {{ strtoupper($condition->product->brand) }}
                            </span>
                        </div>

                        <!-- Product Details -->
                        <div>
                            <div class="font-mono text-xs text-brand-grey uppercase tracking-wider mb-1">
                                {{ $condition->product->category->name ?? 'SMARTPHONE' }}
                            </div>
                            <h3 class="font-sans font-bold text-brand-charcoal text-lg group-hover:text-brand-red transition-colors leading-snug">
                                {{ $condition->product->name }}
                            </h3>
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
                                        ${{ number_format($condition->price, 2) }}
                                    </span>
                                    @if($condition->price < $condition->product->base_price)
                                        <span class="font-mono text-xs text-brand-grey line-through">
                                            ${{ number_format($condition->product->base_price, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <button type="button" class="font-mono text-xs uppercase font-bold text-white bg-brand-charcoal group-hover:bg-brand-red px-3 py-2 transition-colors duration-150">
                                View
                            </button>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full border border-dashed border-gray-300 p-12 text-center">
                        <p class="font-mono text-sm text-brand-grey uppercase">No featured product conditions found in database.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </section>

    <!-- Exchange / Trade-In Valuation Banner -->
    <section id="trade-in" class="py-16 bg-brand-offwhite border-y-2 border-brand-red">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                
                <div class="max-w-2xl">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">EXCHANGE PROGRAM</span>
                    <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal mt-1">
                        Have an old phone? Trade it in for instant credit.
                    </h2>
                    <p class="mt-3 font-sans text-base text-brand-grey leading-relaxed">
                        We accept devices in any condition. Bring your phone into FoneX Store or request an online valuation to upgrade to a new or certified pre-owned model seamlessly.
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <a href="#trade-in-request" class="inline-flex items-center space-x-3 bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase tracking-wider font-bold px-8 py-4 transition-colors duration-150 shadow-sm">
                        <span>Get Instant Device Valuation</span>
                        <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </section>

@endsection
