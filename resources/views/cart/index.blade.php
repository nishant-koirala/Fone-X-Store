@extends('layouts.storefront')

@section('title', 'Shopping Cart — FoneX Store')

@section('content')

    <!-- Stark Page Header -->
    <div class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-brand-white leading-none">
                Shopping Cart
            </h1>
        </div>
    </div>

    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-16 bg-brand-white min-h-[60vh]">
        
        @if(count($cartItems) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                
                <!-- Left: Cart Items (List) -->
                <div class="lg:col-span-8">
                    
                    <div class="flex items-center space-x-4 mb-8">
                        <span class="flex items-center justify-center h-8 w-8 bg-brand-charcoal text-brand-white font-mono text-sm font-bold">1</span>
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Review Your Items</h2>
                    </div>

                    <div class="border-t-2 border-brand-charcoal">
                        @foreach($cartItems as $index => $item)
                            <div class="py-8 border-b-2 border-brand-charcoal flex flex-col sm:flex-row gap-6 relative group">
                                
                                <!-- Remove Button -->
                                <form action="{{ route('cart.destroy', $item['condition']->id) }}" method="POST" class="absolute top-8 right-0 sm:right-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-brand-grey hover:text-brand-red p-2 transition-colors">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>

                                <!-- Image -->
                                <div class="w-full sm:w-40 h-40 bg-brand-offwhite border-2 border-brand-charcoal flex items-center justify-center flex-shrink-0 p-4">
                                    @if($item['condition']->product->image)
                                        <img src="{{ Storage::url($item['condition']->product->image) }}" alt="{{ $item['condition']->product->name }} image" class="max-h-full w-auto object-contain mix-blend-multiply" />
                                    @else
                                        <x-product-icon :categorySlug="$item['condition']->product->category->slug ?? ''" class="h-16 text-brand-charcoal stroke-[1]" />
                                    @endif
                                </div>

                                <!-- Details -->
                                <div class="flex-grow flex flex-col justify-between pr-10 sm:pr-12">
                                    <div>
                                        <div class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey mb-1">
                                            {{ $item['condition']->product->brand }}
                                        </div>
                                        <h3 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-2">
                                            <a href="{{ route('products.show', $item['condition']->product->slug) }}" class="hover:text-brand-red transition-colors">{{ $item['condition']->product->name }}</a>
                                        </h3>
                                        <div class="inline-block border border-brand-charcoal px-2 py-0.5 font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white">
                                            CONDITION: {{ strtoupper($item['condition']->grade === 'new' ? 'Brand New' : 'Grade ' . $item['condition']->grade) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-end justify-between mt-6">
                                        <form action="{{ route('cart.update', $item['condition']->id) }}" method="POST" class="flex items-center border-2 border-brand-charcoal bg-brand-white w-24">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['condition']->quantity_in_stock }}" class="w-full text-center font-mono font-bold border-none focus:ring-0 p-2" onchange="this.form.submit()">
                                        </form>
                                        
                                        <div class="text-right">
                                            <div class="font-mono text-xl font-bold text-brand-charcoal">
                                                Rs {{ number_format($item['condition']->price * $item['quantity']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-8 flex justify-between items-center">
                        <a href="{{ route('products.index') }}" class="font-mono text-xs uppercase font-bold text-brand-grey hover:text-brand-charcoal underline decoration-brand-grey hover:decoration-brand-charcoal underline-offset-4 transition-all">
                            &larr; Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Right: Order Summary (Sticky) -->
                <div class="lg:col-span-4">
                    <div class="sticky top-28 bg-brand-offwhite border-2 border-brand-charcoal p-8 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                        <h3 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-6 pb-4 border-b-2 border-brand-charcoal">Order Summary</h3>
                        
                        <div class="space-y-4 font-mono text-sm font-bold text-brand-charcoal">
                            <div class="flex justify-between">
                                <span class="text-brand-grey">Subtotal ({{ collect($cartItems)->sum('quantity') }} items)</span>
                                <span>Rs {{ number_format($total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-brand-grey">Shipping Estimate</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-brand-grey">Taxes</span>
                                <span>Included</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t-2 border-brand-charcoal flex justify-between items-end">
                            <span class="font-mono text-sm font-bold uppercase tracking-widest text-brand-charcoal">Total</span>
                            <span class="font-mono text-3xl font-bold text-brand-red">Rs {{ number_format($total) }}</span>
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm uppercase tracking-widest font-bold px-6 py-5 transition-colors duration-300 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none translate-y-0 hover:translate-y-1 hover:translate-x-1">
                                Proceed to Checkout &rarr;
                            </a>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t-2 border-brand-charcoal">
                            <div class="flex items-center space-x-2 text-brand-charcoal">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="font-mono text-xs font-bold uppercase tracking-widest">Secure Cash on Delivery</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @else
            <!-- Empty Cart State -->
            <div class="max-w-2xl mx-auto border-4 border-brand-charcoal p-16 text-center bg-brand-offwhite shadow-[12px_12px_0px_0px_rgba(42,42,44,1)]">
                <svg class="mx-auto h-20 w-20 text-brand-charcoal stroke-[1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="square" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="mt-6 font-display text-4xl uppercase tracking-tight text-brand-charcoal">Your cart is empty.</h2>
                <p class="mt-4 font-sans text-brand-charcoal/80">Looks like you haven't made your choice yet.</p>
                <div class="mt-10">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-3 bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-8 py-5 transition-colors duration-300">
                        <span>Shop Inventory</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @endif

    </div>

@endsection
