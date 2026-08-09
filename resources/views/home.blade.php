@extends('layouts.storefront')

@section('title', 'FoneX Store — Next-Gen Smartphones & Certified Pre-Owned Inventory')

@section('content')

    <!-- Rich Ambient Dark Hero Section -->
    <section class="relative bg-brand-charcoal text-white overflow-hidden py-16 sm:py-24 border-b border-white/10">
        <!-- Radial Spotlight Ambient Background Glows -->
        <div class="absolute -top-32 left-1/4 h-96 w-96 rounded-full bg-brand-red/30 blur-[120px] pointer-events-none"></div>
        <div class="absolute top-1/2 -right-20 h-96 w-96 rounded-full bg-rose-600/20 blur-[140px] pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Copy & Action Buttons -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    <!-- Eyebrow Pill -->
                    <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1 text-xs font-mono font-bold tracking-wider text-brand-red uppercase">
                        <span class="h-2 w-2 rounded-full bg-brand-red animate-ping"></span>
                        <span class="text-white">DIRECT IMPORTS & CERTIFIED PRE-OWNED PHONES</span>
                    </div>

                    <!-- Radiant Gradient Headline -->
                    <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-white leading-none">
                        Every Phone. <br>
                        <span class="gradient-text-hero">Every Condition.</span> <br>
                        One Store.
                    </h1>

                    <p class="font-sans text-base sm:text-lg text-white/80 max-w-xl font-light leading-relaxed">
                        Buy brand-new sealed smartphones with official warranty or shop 100-point inspected Grade A, B, C pre-owned devices. Trade in your current phone for instant store credit.
                    </p>

                    <!-- CTA Actions -->
                    <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('products.index') }}" class="group relative inline-flex items-center justify-center space-x-3 bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider px-8 py-4 shadow-lg shadow-brand-red/30 transition-all duration-200">
                            <span>Explore Phone Inventory</span>
                            <svg class="h-4 w-4 stroke-[2.5] transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="{{ route('trade-in.create') }}" class="inline-flex items-center justify-center space-x-2 border border-white/25 hover:border-white bg-white/5 hover:bg-white/10 text-white font-mono text-xs uppercase font-bold tracking-wider px-6 py-4 backdrop-blur-md transition-all">
                            <span>Trade-In Old Phone</span>
                        </a>
                    </div>

                    <!-- Trust Stats Counter Strip -->
                    <div class="pt-8 border-t border-white/10 grid grid-cols-3 gap-4 font-mono text-xs">
                        <div>
                            <div class="font-display text-xl sm:text-2xl text-white">5,000+</div>
                            <div class="text-white/60 text-[11px] uppercase">Phones Delivered</div>
                        </div>
                        <div>
                            <div class="font-display text-xl sm:text-2xl text-white">100-PT</div>
                            <div class="text-white/60 text-[11px] uppercase">Quality Checklist</div>
                        </div>
                        <div>
                            <div class="font-display text-xl sm:text-2xl text-white">7-DAY</div>
                            <div class="text-white/60 text-[11px] uppercase">Testing Return</div>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Floating Phone Presentation Stage -->
                <div class="lg:col-span-5 flex justify-center relative">
                    <!-- Glowing Pedestal Base -->
                    <div class="absolute bottom-0 h-32 w-64 bg-brand-red/40 blur-3xl rounded-full"></div>

                    <!-- Floating Phone Silhouette Card -->
                    <div class="animate-float relative z-10 w-full max-w-sm glass-panel-dark p-8 border border-white/20 shadow-2xl text-center space-y-6">
                        
                        <!-- Header Tag -->
                        <div class="flex items-center justify-between font-mono text-xs">
                            <span class="bg-brand-red text-white px-2 py-0.5 font-bold uppercase tracking-wider text-[10px]">
                                FEATURED DEVICE
                            </span>
                            <span class="text-white/70">100-PT PASSED</span>
                        </div>

                        <!-- Phone Graphic Silhouette Contour -->
                        <div class="aspect-[4/3] bg-white/5 border border-white/10 flex items-center justify-center relative overflow-hidden group">
                            <svg class="h-32 w-32 text-brand-red/80 stroke-[1.2] transform group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                                <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                            </svg>
                            <!-- Live Spec Badge -->
                            <div class="absolute bottom-2 left-2 bg-black/70 backdrop-blur-md px-2 py-1 font-mono text-[10px] text-white">
                                APPLE • GRADE A
                            </div>
                        </div>

                        <!-- Device Info & Pricing -->
                        <div class="space-y-2">
                            <h4 class="font-display text-xl uppercase text-white">iPhone 15 Pro Max</h4>
                            <div class="flex items-center justify-center space-x-2 font-mono">
                                <span class="font-bold text-2xl text-white">Rs 155,000</span>
                                <span class="text-xs text-white/50 line-through">Rs 185,000</span>
                            </div>
                        </div>

                        <!-- Floating Live Badges -->
                        <div class="space-y-2 font-mono text-xs">
                            <div class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-3 py-1.5 text-[11px] uppercase">
                                ✓ IN STOCK • 7-DAY RETURN GUARANTEE
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Three-Path Selector (01 / 02 / 03) -->
    <section class="bg-white py-12 relative z-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Path 01: Direct Deals (New) -->
                <a href="{{ route('products.index', ['grade' => 'new']) }}" class="group glass-panel p-8 border border-gray-200 hover:border-brand-red card-glow-hover relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red bg-brand-red/10 px-2 py-1">01 / DIRECT DEALS</span>
                        <svg class="h-5 w-5 text-brand-charcoal transform group-hover:translate-x-1 group-hover:text-brand-red transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal group-hover:text-brand-red transition-colors">
                        Brand New Phones
                    </h3>
                    <p class="mt-2 font-sans text-sm text-brand-grey leading-relaxed">
                        Factory sealed devices with official manufacturer brand warranty.
                    </p>
                </a>

                <!-- Path 02: Graded Inventory (Certified Used) -->
                <a href="{{ route('products.index', ['grade' => 'used']) }}" class="group glass-panel p-8 border border-gray-200 hover:border-brand-red card-glow-hover relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red bg-brand-red/10 px-2 py-1">02 / GRADED INVENTORY</span>
                        <svg class="h-5 w-5 text-brand-charcoal transform group-hover:translate-x-1 group-hover:text-brand-red transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal group-hover:text-brand-red transition-colors">
                        Certified Used
                    </h3>
                    <p class="mt-2 font-sans text-sm text-brand-grey leading-relaxed">
                        Rigorously tested Grade A, B & C pre-owned phones with 7-day checking guarantee.
                    </p>
                </a>

                <!-- Path 03: Trade & Upgrade -->
                <a href="{{ route('trade-in.create') }}" class="group glass-panel p-8 border border-gray-200 hover:border-brand-red card-glow-hover relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red bg-brand-red/10 px-2 py-1">03 / INSTANT EXCHANGE</span>
                        <svg class="h-5 w-5 text-brand-charcoal transform group-hover:translate-x-1 group-hover:text-brand-red transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal group-hover:text-brand-red transition-colors">
                        Trade & Upgrade
                    </h3>
                    <p class="mt-2 font-sans text-sm text-brand-grey leading-relaxed">
                        Bring your old device for instant credit valuation toward your next phone purchase.
                    </p>
                </a>

            </div>
        </div>
    </section>

    <!-- Trust Bar Strip -->
    <div class="bg-brand-offwhite border-y border-gray-200 py-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center font-mono text-xs font-bold text-brand-charcoal uppercase tracking-wider">
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-brand-red font-bold text-base">✓</span>
                    <span>100-POINT DIAGNOSTIC TEST</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-brand-red font-bold text-base">✓</span>
                    <span>7-DAY CHECKING GUARANTEE</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-brand-red font-bold text-base">✓</span>
                    <span>FAST NEPALWIDE DELIVERY</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-brand-red font-bold text-base">✓</span>
                    <span>EASY EMI INSTALMENTS</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products Section -->
    <section class="py-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 pb-4 border-b border-gray-200 gap-4">
                <div>
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">IN STOCK & READY TO SHIP</span>
                    <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal mt-1">
                        Featured Inventory
                    </h2>
                </div>
                <div>
                    <a href="{{ route('products.index') }}" class="font-mono text-xs uppercase font-bold text-brand-charcoal hover:text-brand-red transition-colors flex items-center space-x-1">
                        <span>View All Listings</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($featuredConditions as $condition)
                    <div class="group card-glow-hover relative flex flex-col justify-between border border-gray-200 bg-white p-5">
                        
                        <!-- Top Header Tags -->
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

                        <!-- Product Image Placeholder: Shine Overlay Contour -->
                        <div class="shine-container aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-6 relative overflow-hidden mb-5 border border-gray-100">
                            <x-product-icon :categorySlug="$condition->product->category->slug ?? ''" class="h-28 w-28 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors duration-300 stroke-[1.5]" />

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

                        <!-- Product Details -->
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

                        <!-- Price & Action Link -->
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
                    <div class="col-span-full border border-dashed border-gray-300 p-12 text-center bg-brand-offwhite">
                        <p class="font-mono text-sm text-brand-grey uppercase">No featured product conditions found in database.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Trade-In / Exchange Program Banner -->
    <section class="py-16 bg-brand-charcoal text-white relative overflow-hidden border-t-2 border-brand-red">
        <div class="absolute -right-20 top-0 h-96 w-96 bg-brand-red/30 blur-[130px] pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                
                <div class="max-w-2xl space-y-4">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red bg-white/10 px-2.5 py-1">
                        INSTANT DEVICE EXCHANGE
                    </span>
                    <h2 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-white">
                        Have an old phone? Trade it in for instant store credit.
                    </h2>
                    <p class="font-sans text-base text-white/80 leading-relaxed font-light">
                        We accept phones in any condition. Submit your device details online for an instant valuation estimate and upgrade to a brand new or certified used phone seamlessly.
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <a href="{{ route('trade-in.create') }}" class="inline-flex items-center space-x-3 bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase tracking-wider font-bold px-8 py-4 transition-all duration-150 shadow-lg shadow-brand-red/30">
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
