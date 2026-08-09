@extends('layouts.storefront')

@section('title', 'Shopping Cart — FoneX Store')

@section('content')

    <!-- Cart Page Header -->
    <div class="bg-brand-offwhite border-b border-gray-200 py-8">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">CHECKOUT FLOW</span>
                    <h1 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal mt-1">
                        Your Shopping Cart
                    </h1>
                </div>
                <div class="font-mono text-xs text-brand-grey">
                    ITEMS IN CART: <span class="font-bold text-brand-charcoal">{{ count($cartItems) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Cart Container -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12">
        @if(!empty($cartItems))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                
                <!-- Left Column: Cart Line Items -->
                <div class="lg:col-span-8 border border-gray-200 bg-white">
                    <div class="border-b border-gray-200 bg-brand-offwhite px-6 py-4 font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal hidden sm:grid sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-6">Product Item</div>
                        <div class="sm:col-span-2 text-center">Unit Price</div>
                        <div class="sm:col-span-2 text-center">Quantity</div>
                        <div class="sm:col-span-2 text-right">Line Total</div>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @foreach($cartItems as $item)
                            @php 
                                $cond = $item['condition']; 
                                $prod = $cond->product;
                            @endphp
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                                
                                <!-- Product Details & SVG Silhouette Icon -->
                                <div class="sm:col-span-6 flex items-center space-x-4">
                                    <div class="h-16 w-16 flex-shrink-0 bg-brand-offwhite border border-gray-200 flex items-center justify-center p-2">
                                        <svg class="h-10 w-10 text-brand-charcoal/30 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                            <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
                                            <circle cx="12" cy="19" r="0.75" fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            @if(strtolower($cond->grade) === 'new')
                                                <span class="border border-brand-red text-brand-red font-mono text-[10px] font-bold uppercase px-1.5 py-0.5">
                                                    NEW
                                                </span>
                                            @else
                                                <span class="bg-brand-red text-white font-mono text-[10px] font-bold uppercase px-1.5 py-0.5">
                                                    GRADE {{ strtoupper($cond->grade) }}
                                                </span>
                                            @endif
                                            <span class="font-mono text-[10px] text-brand-grey uppercase">
                                                {{ $prod->brand }}
                                            </span>
                                        </div>
                                        <a href="{{ route('products.show', $prod->slug) }}" class="font-sans font-bold text-brand-charcoal text-base hover:text-brand-red transition-colors block mt-1">
                                            {{ $prod->name }}
                                        </a>
                                        <p class="font-mono text-[11px] text-brand-grey">
                                            Max available: {{ $cond->quantity_in_stock }} units
                                        </p>
                                    </div>
                                </div>

                                <!-- Unit Price -->
                                <div class="sm:col-span-2 text-left sm:text-center font-mono text-sm font-semibold text-brand-charcoal">
                                    <span class="sm:hidden font-bold text-brand-grey text-xs">Unit: </span>
                                    Rs {{ number_format($cond->price) }}
                                </div>

                                <!-- Quantity Selector Form -->
                                <div class="sm:col-span-2 flex items-center justify-start sm:justify-center">
                                    <form method="POST" action="{{ route('cart.update', $cond->id) }}" class="flex items-center space-x-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="quantity" onchange="this.form.submit()" class="font-mono text-xs border border-gray-300 bg-white text-brand-charcoal px-2 py-1 focus:border-brand-red focus:ring-0">
                                            @for($i = 1; $i <= min(10, $cond->quantity_in_stock); $i++)
                                                <option value="{{ $i }}" {{ $item['quantity'] == $i ? 'selected' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </form>
                                </div>

                                <!-- Line Total & Remove Action -->
                                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end space-x-4">
                                    <span class="font-mono font-bold text-brand-charcoal text-base">
                                        Rs {{ number_format($item['line_total']) }}
                                    </span>
                                    <form method="POST" action="{{ route('cart.destroy', $cond->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-brand-grey hover:text-brand-red transition-colors p-1" title="Remove Item">
                                            <svg class="h-5 w-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="square" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right Column: Order Summary Card -->
                <div class="lg:col-span-4 border border-gray-200 bg-white p-6 space-y-6">
                    <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal border-b border-gray-200 pb-3">
                        Order Summary
                    </h2>

                    <div class="space-y-3 font-mono text-xs">
                        <div class="flex items-center justify-between text-brand-grey">
                            <span>Subtotal</span>
                            <span class="font-bold text-brand-charcoal text-sm">Rs {{ number_format($subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-brand-grey">
                            <span>Standard Delivery</span>
                            <span class="font-bold text-emerald-600">FREE</span>
                        </div>
                        <div class="flex items-center justify-between text-brand-grey">
                            <span>7-Day Check Guarantee</span>
                            <span class="font-bold text-emerald-600">INCLUDED</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex items-baseline justify-between text-brand-charcoal">
                            <span class="font-bold uppercase">Total Payable</span>
                            <span class="font-bold text-2xl text-brand-charcoal">Rs {{ number_format($subtotal) }}</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('checkout.index') }}" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2">
                            <span>Proceed to Checkout</span>
                            <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="border-t border-gray-100 pt-4 text-center font-mono text-[11px] text-brand-grey">
                        Payment collected via Cash on Delivery or Pay on Pickup in Nepal.
                    </div>
                </div>

            </div>
        @else
            <!-- Empty Cart State -->
            <div class="border border-dashed border-gray-300 p-16 text-center bg-brand-offwhite max-w-2xl mx-auto">
                <svg class="mx-auto h-16 w-16 text-brand-grey stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="square" stroke-linejoin="miter" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h2 class="mt-4 font-display text-2xl uppercase tracking-tight text-brand-charcoal">Your Shopping Cart is Empty</h2>
                <p class="mt-2 font-sans text-sm text-brand-grey max-w-md mx-auto">
                    You have not added any phones to your shopping cart yet. Browse our inventory of brand-new and certified pre-owned smartphones.
                </p>
                <div class="mt-8">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-2 bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider px-8 py-4 transition-colors">
                        <span>Browse Phones Catalog</span>
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

@endsection
