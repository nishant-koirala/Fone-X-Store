@extends('layouts.storefront')

@section('title', 'FoneX Store — Next-Gen Smartphones & Certified Pre-Owned')

@section('content')

    <!-- Stark Split-Screen Hero -->
    <section class="border-b-2 border-brand-charcoal bg-brand-white">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- Left: Typography & Action -->
            <div class="px-6 py-16 lg:p-24 flex flex-col justify-center">
                <div class="mb-8">
                    <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                        DIRECT IMPORTS & CERTIFIED PRE-OWNED
                    </span>
                </div>
                
                <h1 class="font-display text-6xl sm:text-7xl lg:text-8xl uppercase tracking-tight text-brand-charcoal leading-[0.85] mb-8">
                    Every Phone.<br>
                    <span class="text-brand-red">Every Condition.</span><br>
                    One Store.
                </h1>
                
                <p class="font-sans text-lg sm:text-xl text-brand-charcoal/80 max-w-md font-light leading-relaxed mb-10">
                    Buy brand-new sealed smartphones with official warranty or shop 100-point inspected Grade A/B/C pre-owned devices.
                </p>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                    <a href="{{ route('products.index') }}" class="group relative inline-flex items-center justify-center space-x-3 bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-8 py-5 transition-colors duration-300">
                        <span>Shop Inventory</span>
                        <svg class="h-4 w-4 stroke-[2.5] transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="{{ route('trade-in.create') }}" class="inline-flex items-center justify-center space-x-2 border-2 border-brand-charcoal hover:bg-brand-charcoal hover:text-brand-white text-brand-charcoal font-mono text-xs uppercase font-bold tracking-widest px-8 py-5 transition-colors duration-300">
                        <span>Trade-In Old Phone</span>
                    </a>
                </div>
            </div>
            
            <!-- Right: Asymmetric Image Grid or Stark Single Image -->
            <div class="bg-brand-offwhite border-t-2 lg:border-t-0 lg:border-l-2 border-brand-charcoal relative overflow-hidden flex items-center justify-center min-h-[500px]">
                <!-- Decorative geometric patterns instead of blobs -->
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#2A2A2C 2px, transparent 2px); background-size: 32px 32px;"></div>
                
                @if(isset($heroConditions) && $heroConditions->count() > 0)
                    @php $hero = $heroConditions->first(); @endphp
                    <div class="relative z-10 text-center flex flex-col items-center">
                        @if($hero->product->image)
                            <img src="{{ Storage::url($hero->product->image) }}" alt="{{ $hero->product->name }}" class="h-64 sm:h-80 object-contain drop-shadow-2xl mix-blend-multiply" />
                        @else
                            <x-product-icon :categorySlug="$hero->product->category->slug ?? ''" class="h-64 sm:h-80 text-brand-charcoal stroke-[1] drop-shadow-2xl" />
                        @endif
                        
                        <!-- Floating Spec Box -->
                        <div class="mt-8 bg-brand-white border-2 border-brand-charcoal px-6 py-4 shadow-[8px_8px_0px_0px_rgba(200,32,45,1)]">
                            <div class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey mb-1">{{ $hero->product->brand }} • NEW IN STOCK</div>
                            <div class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">{{ $hero->product->name }}</div>
                            <div class="font-mono font-bold text-brand-red text-xl mt-2">Rs {{ number_format($hero->price) }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Trust Bar Strip (Minimal) -->
    <div class="bg-brand-charcoal py-4 border-b-4 border-brand-red">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center text-center font-mono text-xs sm:text-xs font-bold text-brand-white uppercase tracking-widest gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-brand-red text-lg leading-none">✓</span>
                    <span>100-Point Diagnostic Test</span>
                </div>
                <div class="hidden sm:block text-brand-white/20">|</div>
                <div class="flex items-center space-x-2">
                    <span class="text-brand-red text-lg leading-none">✓</span>
                    <span>7-Day Checking Guarantee</span>
                </div>
                <div class="hidden sm:block text-brand-white/20">|</div>
                <div class="flex items-center space-x-2">
                    <span class="text-brand-red text-lg leading-none">✓</span>
                    <span>Fast Nepal-Wide Delivery</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Asymmetrical Pathways (Bento Box Style) -->
    <section class="py-24 bg-brand-white border-b-2 border-brand-charcoal">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            
            <x-section-heading 
                eyebrow="How to Shop"
                title="Three ways to get your next device."
            />

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-12">
                
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
    <section class="py-24 bg-brand-offwhite">
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

    <!-- Trade-In Banner (Stark Typographic) -->
    <section class="border-y-2 border-brand-charcoal bg-brand-charcoal relative overflow-hidden">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-24 lg:py-32 flex flex-col lg:flex-row items-center justify-between gap-12">
            
            <div class="max-w-3xl">
                <h2 class="font-display text-5xl sm:text-7xl uppercase tracking-tight text-brand-white leading-[0.9]">
                    DON'T LET YOUR <br>
                    OLD PHONE <span class="text-brand-red">GATHER DUST.</span>
                </h2>
                <p class="font-sans text-xl text-brand-white/80 mt-8 max-w-xl font-light">
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
