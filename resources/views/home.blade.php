@extends('layouts.storefront')

@section('title', 'FoneX Store — Next-Gen Smartphones & Certified Pre-Owned')

@section('content')

    <!-- Above-The-Fold First Screen (Hero + Trust Bar) -->
    <div class="min-h-[calc(100vh-100px)] lg:h-[calc(100vh-100px)] flex flex-col justify-between">
        
        <!-- Stark Split-Screen Hero -->
        <section class="flex-grow flex items-stretch border-b-2 border-brand-charcoal bg-brand-white min-h-0">
            <div class="grid grid-cols-1 lg:grid-cols-2 w-full h-full">
                <!-- Left: Typography & Action -->
                <div class="px-6 py-4 sm:py-6 lg:px-12 xl:px-16 flex flex-col justify-center">
                    <div class="mb-3 sm:mb-4">
                        <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                            DIRECT IMPORTS & CERTIFIED PRE-OWNED
                        </span>
                    </div>
                    
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-4xl xl:text-5xl 2xl:text-6xl uppercase tracking-tight text-brand-charcoal leading-[0.9] mb-3 sm:mb-4">
                        Every Phone.<br>
                        <span class="text-brand-red">Every Condition.</span><br>
                        One Store.
                    </h1>
                    
                    <p class="font-sans text-sm sm:text-base text-brand-charcoal/80 max-w-lg font-light leading-relaxed mb-5 sm:mb-6">
                        Buy brand-new sealed smartphones with official warranty or shop 100-point inspected Grade A/B/C pre-owned devices.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="{{ route('products.index') }}" class="group relative inline-flex items-center justify-center space-x-3 bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-6 py-3.5 sm:px-7 sm:py-3.5 transition-colors duration-300">
                            <span>Shop Inventory</span>
                            <svg class="h-4 w-4 stroke-[2.5] transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="{{ route('trade-in.create') }}" class="inline-flex items-center justify-center space-x-2 border-2 border-brand-charcoal hover:bg-brand-charcoal hover:text-brand-white text-brand-charcoal font-mono text-xs uppercase font-bold tracking-widest px-6 py-3.5 sm:px-7 sm:py-3.5 transition-colors duration-300">
                            <span>Trade-In Old Phone</span>
                        </a>
                    </div>
                </div>
                
                <!-- Right: Rotating Featured Products Carousel -->
                <div x-data="{
                        active: 0,
                        total: {{ count($heroConditions) }},
                        autoplay: null,
                        startAutoplay() {
                            this.stopAutoplay();
                            if (this.total > 1) {
                                this.autoplay = setInterval(() => {
                                    this.next();
                                }, 4000);
                            }
                        },
                        stopAutoplay() {
                            if (this.autoplay) clearInterval(this.autoplay);
                        },
                        next() {
                            this.active = (this.active + 1) % this.total;
                        },
                        prev() {
                            this.active = (this.active - 1 + this.total) % this.total;
                        }
                    }"
                    x-init="startAutoplay()"
                    @mouseenter="stopAutoplay()"
                    @mouseleave="startAutoplay()"
                    class="bg-brand-offwhite border-t-2 lg:border-t-0 lg:border-l-2 border-brand-charcoal relative overflow-hidden flex flex-col justify-center items-center w-full h-[450px] sm:h-[520px] lg:h-full min-h-0 p-0">
                    
                    <!-- Decorative geometric patterns (subtle underlay) -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#2A2A2C 2px, transparent 2px); background-size: 32px 32px;"></div>

                    <!-- Featured Phone Badge Ticker -->
                    <div class="absolute top-4 left-4 z-30 flex items-center space-x-2 font-mono text-[10px] font-bold uppercase tracking-wider text-brand-charcoal bg-brand-white/95 backdrop-blur-sm border-2 border-brand-charcoal px-3 py-1 shadow-[3px_3px_0px_0px_rgba(42,42,44,1)] pointer-events-none">
                        <span class="h-2 w-2 rounded-full bg-brand-red animate-pulse"></span>
                        <span>FEATURED SPOTLIGHT</span>
                    </div>

                    @if(isset($heroConditions) && $heroConditions->count() > 0)
                        <!-- Carousel Slides Container: Full-Bleed Absolute Fill -->
                        <div class="relative w-full h-full flex-grow overflow-hidden">
                            @foreach($heroConditions as $index => $hero)
                                <div x-show="active === {{ $index }}"
                                     x-transition:enter="transition-all ease-out duration-700"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition-all ease-in duration-400 absolute"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute inset-0 w-full h-full">
                                    
                                    <a href="{{ route('products.show', $hero->product->slug) }}" class="group/slide block relative w-full h-full overflow-hidden">
                                        <!-- Full-Bleed Section-Filling Image -->
                                        @if($hero->product->image)
                                            <img src="{{ Storage::url($hero->product->image) }}" 
                                                 alt="{{ $hero->product->name }}" 
                                                 class="w-full h-full object-cover object-center group-hover/slide:scale-105 transition-transform duration-700 ease-out" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-brand-offwhite p-8">
                                                <x-product-icon :categorySlug="$hero->product->category->slug ?? ''" class="w-48 h-48 sm:w-64 sm:h-64 text-brand-charcoal stroke-[1] drop-shadow-2xl group-hover/slide:scale-105 transition-transform duration-300" />
                                            </div>
                                        @endif

                                        <!-- Subtle Bottom Vignette Gradient for Overlay Contrast -->
                                        <div class="absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-brand-charcoal/80 via-brand-charcoal/30 to-transparent pointer-events-none"></div>

                                        <!-- Sleek Floating Compact Spec Card -->
                                        <div class="absolute bottom-11 left-1/2 -translate-x-1/2 z-20 w-[90%] max-w-xs text-center pointer-events-auto">
                                            <div class="bg-brand-white/95 backdrop-blur-sm border-2 border-brand-charcoal px-4 py-2 shadow-[4px_4px_0px_0px_rgba(200,32,45,1)] group-hover/slide:shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] group-hover/slide:border-brand-red transition-all duration-200">
                                                <div class="font-mono text-[9px] font-bold uppercase tracking-wider text-brand-grey truncate">
                                                    {{ $hero->product->brand }} • {{ ($hero->grade instanceof \App\Enums\GradeEnum ? $hero->grade === \App\Enums\GradeEnum::NEW : $hero->grade === 'new') ? 'NEW IN STOCK' : 'GRADE ' . ($hero->grade instanceof \App\Enums\GradeEnum ? $hero->grade->value : $hero->grade) . ' • CERTIFIED' }}
                                                </div>
                                                <div class="font-display text-xs sm:text-sm uppercase tracking-tight text-brand-charcoal group-hover/slide:text-brand-red transition-colors font-bold truncate">
                                                    {{ $hero->product->name }}
                                                </div>
                                                <div class="font-mono font-bold text-brand-red text-xs">
                                                    Rs {{ number_format($hero->price) }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Carousel Controls & Indicators -->
                        @if($heroConditions->count() > 1)
                            <!-- Side Navigation Arrows -->
                            <button type="button" @click.prevent="prev(); startAutoplay();" aria-label="Previous Featured Phone" class="absolute left-3 top-1/2 -translate-y-1/2 z-30 p-2 border-2 border-brand-charcoal bg-brand-white/90 hover:bg-brand-charcoal hover:text-brand-white text-brand-charcoal transition-all shadow-[2px_2px_0px_0px_rgba(42,42,44,1)]">
                                <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button type="button" @click.prevent="next(); startAutoplay();" aria-label="Next Featured Phone" class="absolute right-3 top-1/2 -translate-y-1/2 z-30 p-2 border-2 border-brand-charcoal bg-brand-white/90 hover:bg-brand-charcoal hover:text-brand-white text-brand-charcoal transition-all shadow-[2px_2px_0px_0px_rgba(42,42,44,1)]">
                                <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Bottom Indicator Dots / Bars -->
                            <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 z-30 flex items-center space-x-1.5 pointer-events-auto">
                                @foreach($heroConditions as $index => $hero)
                                    <button type="button" 
                                            @click="active = {{ $index }}; startAutoplay();" 
                                            aria-label="Go to slide {{ $index + 1 }}"
                                            :class="active === {{ $index }} ? 'w-6 bg-brand-red' : 'w-2 bg-brand-white/70 hover:bg-brand-white'" 
                                            class="h-1.5 border border-brand-charcoal transition-all duration-300 shadow-[1px_1px_0px_0px_rgba(42,42,44,1)]">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </section>

        <!-- Trust Bar Strip (Minimal) -->
        <div class="bg-brand-charcoal py-3 sm:py-3.5 border-b-4 border-brand-red flex-shrink-0">
            <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center text-center font-mono text-[11px] sm:text-xs font-bold text-brand-white uppercase tracking-wider sm:tracking-widest gap-2 sm:gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red text-base leading-none">✓</span>
                        <span>100-Point Diagnostic Test</span>
                    </div>
                    <div class="hidden sm:block text-brand-white/20">|</div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red text-base leading-none">✓</span>
                        <span>7-Day Checking Guarantee</span>
                    </div>
                    <div class="hidden sm:block text-brand-white/20">|</div>
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-red text-base leading-none">✓</span>
                        <span>Fast Nepal-Wide Delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Asymmetrical Pathways (Bento Box Style) -->
    <section class="py-12 sm:py-16 bg-brand-white border-b-2 border-brand-charcoal">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            
            <x-section-heading 
                eyebrow="How to Shop"
                title="Three ways to get your next device."
            />

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-8">
                
                <!-- Path 01: Direct Deals (Large Block) -->
                <a href="{{ route('products.index', ['grade' => 'new']) }}" class="group md:col-span-8 bg-brand-offwhite border-2 border-brand-charcoal p-10 hover:bg-brand-charcoal transition-colors duration-300 flex flex-col justify-between min-h-[300px]">
                    <div class="flex items-start justify-between">
                        <x-badge color="charcoal" outline class="group-hover:border-brand-white group-hover:text-brand-white">01 / BRAND NEW</x-badge>
                        <svg class="h-8 w-8 text-brand-charcoal group-hover:text-brand-red transform group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    <div class="mt-12">
                        <h3 class="font-display text-5xl uppercase tracking-tight text-brand-charcoal group-hover:text-brand-white transition-colors">
                            Factory Sealed
                        </h3>
                        <p class="mt-4 font-sans text-lg text-brand-charcoal/70 group-hover:text-brand-white/70 max-w-md">
                            Direct imports with official manufacturer warranty. The absolute pristine experience.
                        </p>
                    </div>
                </a>

                <!-- Path 02 & 03 Stacked on the right -->
                <div class="md:col-span-4 flex flex-col gap-6">
                    
                    <a href="{{ route('products.index', ['grade' => 'used']) }}" class="group flex-1 bg-brand-white border-2 border-brand-charcoal p-8 hover:border-brand-red transition-colors duration-300 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)] hover:shadow-[8px_8px_0px_0px_rgba(200,32,45,1)] flex flex-col justify-between">
                        <div class="flex items-start justify-between mb-8">
                            <x-badge color="red">02 / CERTIFIED USED</x-badge>
                        </div>
                        <div>
                            <h3 class="font-display text-3xl uppercase tracking-tight text-brand-charcoal">
                                Graded Inventory
                            </h3>
                            <p class="mt-2 font-sans text-sm text-brand-charcoal/70">
                                Rigorously tested Grade A, B & C pre-owned phones with 7-day checking guarantee.
                            </p>
                        </div>
                    </a>

                    <a href="{{ route('trade-in.create') }}" class="group flex-1 bg-brand-red border-2 border-brand-charcoal p-8 hover:bg-brand-red-dark transition-colors duration-300 flex flex-col justify-between">
                        <div class="flex items-start justify-between mb-8">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest px-2 py-0.5 border-2 border-brand-charcoal text-brand-charcoal bg-brand-white">03 / INSTANT EXCHANGE</span>
                        </div>
                        <div>
                            <h3 class="font-display text-3xl uppercase tracking-tight text-brand-white">
                                Trade & Upgrade
                            </h3>
                            <p class="mt-2 font-sans text-sm text-brand-white/90">
                                Bring your old device for instant credit valuation toward your next phone purchase.
                            </p>
                        </div>
                    </a>

                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-12 sm:py-16 bg-brand-offwhite">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            
            <x-section-heading 
                eyebrow="In Stock & Ready to Ship"
                title="Featured Inventory"
                actionText="View All Listings"
                actionUrl="{{ route('products.index') }}"
            />

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($featuredConditions as $condition)
                    <x-product-card :condition="$condition" />
                @empty
                    <div class="col-span-full border-2 border-dashed border-brand-charcoal p-12 text-center bg-brand-white">
                        <p class="font-mono text-sm text-brand-charcoal font-bold uppercase tracking-widest">No featured inventory available.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Visit Our Physical Store / Interactive Map -->
    <section class="py-12 sm:py-16 bg-brand-white border-t-2 border-brand-charcoal">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
                <div>
                    <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-offwhite shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] mb-4">
                        RETAIL HUB & INSPECTION LAB
                    </span>
                    <h2 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-brand-charcoal leading-[0.9]">
                        VISIT OUR <span class="text-brand-red">STORE.</span>
                    </h2>
                </div>
                <div class="max-w-md">
                    <p class="font-sans text-brand-charcoal/80 text-base leading-relaxed">
                        Test devices in-person, inspect 100-point diagnostic reports, claim your 7-day checking guarantee, or trade in your old phone on the spot.
                    </p>
                </div>
            </div>

            <!-- Two-column Layout: Store Info + Live Google Map -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                
                <!-- Left Details Box -->
                <div class="lg:col-span-5 flex flex-col justify-between border-2 border-brand-charcoal bg-brand-offwhite p-8 sm:p-10 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <div class="space-y-8">
                        
                        <div>
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">Store Location</span>
                            <h3 class="font-display text-3xl uppercase tracking-tight text-brand-charcoal mt-1">Fone X Store</h3>
                            <p class="font-sans text-brand-charcoal/80 mt-2 text-sm leading-relaxed">
                                Kathmandu, Nepal <br>
                                <span class="text-xs font-mono text-brand-charcoal/60">Coordinates: 27.7366° N, 85.3144° E</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t-2 border-brand-charcoal/10 pt-6">
                            <div>
                                <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">Opening Hours</span>
                                <p class="font-sans text-sm text-brand-charcoal/80 mt-1">
                                    Sun – Fri: 10:00 AM – 7:30 PM <br>
                                    Saturday: Open
                                </p>
                            </div>
                            <div>
                                <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">Walk-In Services</span>
                                <p class="font-sans text-sm text-brand-charcoal/80 mt-1">
                                    Direct Testing, Buying & Instant Trade-in Appraisals
                                </p>
                            </div>
                        </div>

                        <!-- Store Highlights -->
                        <div class="border-t-2 border-brand-charcoal/10 pt-6">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal mb-3 block">What You Can Do In-Store</span>
                            <ul class="space-y-2.5 font-mono text-xs text-brand-charcoal/90">
                                <li class="flex items-center space-x-2">
                                    <span class="text-brand-red font-bold text-sm">✓</span>
                                    <span>Hands-on 100-point physical device verification</span>
                                </li>
                                <li class="flex items-center space-x-2">
                                    <span class="text-brand-red font-bold text-sm">✓</span>
                                    <span>Instant cash or credit trade-in valuations</span>
                                </li>
                                <li class="flex items-center space-x-2">
                                    <span class="text-brand-red font-bold text-sm">✓</span>
                                    <span>Pick up click-and-collect orders on the spot</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Direct Navigation Action -->
                    <div class="mt-8 pt-6 border-t-2 border-brand-charcoal flex flex-col sm:flex-row gap-3">
                        <a href="https://maps.app.goo.gl/ofXysJJNMjXPPYiEA" target="_blank" rel="noopener noreferrer" class="flex-1 inline-flex items-center justify-center space-x-2 bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-6 py-4 transition-colors duration-200 text-center">
                            <span>Open in Google Maps</span>
                            <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <a href="https://maps.app.goo.gl/ofXysJJNMjXPPYiEA" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center space-x-2 border-2 border-brand-charcoal hover:bg-brand-charcoal hover:text-brand-white text-brand-charcoal font-mono text-xs uppercase font-bold tracking-widest px-5 py-4 transition-colors duration-200">
                            <span>Get Directions</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Interactive Google Maps Iframe -->
                <div class="lg:col-span-7 border-2 border-brand-charcoal bg-brand-offwhite shadow-[8px_8px_0px_0px_rgba(42,42,44,1)] overflow-hidden relative min-h-[420px] lg:min-h-full">
                    <iframe 
                        title="Fone X Store Location"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3531.428616543169!2d85.3121233!3d27.7366239!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb195bb4b404f7%3A0x81a8da5a4722e435!2sFone%20X%20Store!5e0!3m2!1sen!2snp!4v1729757600000!5m2!1sen!2snp" 
                        class="w-full h-full min-h-[450px] border-0" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

            </div>
        </div>
    </section>

    <!-- Trade-In Banner (Stark Typographic) -->
    <section class="border-y-2 border-brand-charcoal bg-brand-charcoal relative overflow-hidden">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-12">
            
            <div class="max-w-3xl">
                <h2 class="font-display text-5xl sm:text-7xl uppercase tracking-tight text-brand-white leading-[0.9]">
                    DON'T LET YOUR <br>
                    OLD PHONE <span class="text-brand-red">GATHER DUST.</span>
                </h2>
                <p class="font-sans text-lg sm:text-xl text-brand-white/80 mt-4 sm:mt-6 max-w-xl font-light">
                    Trade it in for instant store credit. We accept phones in any condition. Get a valuation in 60 seconds.
                </p>
            </div>

            <div class="flex-shrink-0 w-full lg:w-auto">
                <a href="{{ route('trade-in.create') }}" class="block text-center bg-brand-red hover:bg-brand-white hover:text-brand-charcoal text-brand-white font-mono text-sm uppercase tracking-widest font-bold px-10 py-6 border-2 border-transparent hover:border-brand-charcoal transition-colors duration-300 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.1)] hover:shadow-[8px_8px_0px_0px_rgba(200,32,45,1)]">
                    Start Valuation Now
                </a>
            </div>

        </div>
    </section>

@endsection
