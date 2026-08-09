@extends('layouts.storefront')

@section('title', 'Checkout — FoneX Store')

@section('content')

    <!-- Checkout Page Header -->
    <div class="bg-brand-offwhite border-b border-gray-200 py-8">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">FINAL STEP</span>
            <h1 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-brand-charcoal mt-1">
                Checkout & Shipping
            </h1>
        </div>
    </div>

    <!-- Main Checkout Container -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- Left Column: Customer & Delivery Details Form -->
            <div class="lg:col-span-7 border border-gray-200 bg-white p-6 sm:p-8 space-y-6">
                
                <div>
                    <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">
                        Delivery Information
                    </h2>
                    <p class="font-mono text-xs text-brand-grey mt-1">
                        Please provide your contact details for phone verification and courier delivery.
                    </p>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
                    @csrf

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Full Name <span class="text-brand-red">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('name') border-brand-red @enderror"
                               placeholder="e.g. Ram Bahadur Shrestha">
                        @error('name')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number (Required in Nepal) -->
                    <div>
                        <label for="phone" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Primary Phone Number (Required) <span class="text-brand-red">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required 
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('phone') border-brand-red @enderror"
                               placeholder="e.g. 9841234567">
                        <span class="font-mono text-[11px] text-brand-grey mt-1 block">
                            Our team will call this phone number to confirm your order before dispatch.
                        </span>
                        @error('phone')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address (Optional) -->
                    <div>
                        <label for="email" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Email Address (Optional)
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('email') border-brand-red @enderror"
                               placeholder="e.g. ram@example.com">
                        @error('email')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Delivery Address -->
                    <div>
                        <label for="address" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Full Delivery Address <span class="text-brand-red">*</span>
                        </label>
                        <textarea id="address" name="address" rows="3" required 
                                  class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('address') border-brand-red @enderror"
                                  placeholder="e.g. House No. 42, New Road, Ward 22, Kathmandu">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method Option -->
                    <div class="border-t border-gray-200 pt-6 space-y-3">
                        <label class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block">Payment Method</label>
                        <div class="border-2 border-brand-red bg-brand-offwhite p-4 flex items-center space-x-3">
                            <input type="radio" checked readonly class="text-brand-red focus:ring-brand-red">
                            <div>
                                <span class="font-mono text-xs font-bold uppercase text-brand-charcoal block">Cash on Delivery / Pay on Pickup</span>
                                <span class="font-sans text-xs text-brand-grey">Pay in cash or via mobile QR code upon delivery.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Action Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2">
                            <span>Place Order & Confirm Delivery</span>
                            <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

            </div>

            <!-- Right Column: Order Summary Sidebar -->
            <div class="lg:col-span-5 border border-gray-200 bg-white p-6 sm:p-8 space-y-6">
                <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal border-b border-gray-200 pb-3">
                    Order Summary
                </h2>

                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto pr-1">
                    @foreach($cartItems as $item)
                        @php 
                            $cond = $item['condition']; 
                            $prod = $cond->product;
                        @endphp
                        <div class="py-4 flex items-center justify-between">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono text-[10px] font-bold uppercase bg-brand-red text-white px-1.5 py-0.5">
                                        {{ strtoupper($cond->grade) }}
                                    </span>
                                    <span class="font-sans font-bold text-brand-charcoal text-sm">
                                        {{ $prod->name }}
                                    </span>
                                </div>
                                <div class="font-mono text-xs text-brand-grey mt-1">
                                    Qty: {{ $item['quantity'] }} × Rs {{ number_format($cond->price) }}
                                </div>
                            </div>
                            <div class="font-mono font-bold text-brand-charcoal text-sm">
                                Rs {{ number_format($item['line_total']) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-2 font-mono text-xs">
                    <div class="flex items-center justify-between text-brand-grey">
                        <span>Items Subtotal</span>
                        <span>Rs {{ number_format($total) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-brand-grey">
                        <span>Nepal Shipping</span>
                        <span class="text-emerald-600 font-bold">FREE</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex items-baseline justify-between text-brand-charcoal">
                        <span class="font-bold uppercase">Total Amount</span>
                        <span class="font-bold text-2xl text-brand-charcoal">Rs {{ number_format($total) }}</span>
                    </div>
                </div>

                <div class="bg-brand-offwhite border border-gray-200 p-4 font-mono text-xs text-brand-charcoal space-y-1">
                    <div class="font-bold">✓ Transaction Guarantee</div>
                    <div class="text-brand-grey">Stock is locked immediately upon placing order.</div>
                </div>
            </div>

        </div>
    </div>

@endsection
