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
            <div class="lg:col-span-7 border border-gray-200 bg-white p-6 sm:p-8 space-y-6" x-data="{ step: 1 }" data-aos="fade-right">
                
                <!-- Progress Indicator -->
                <div class="flex items-center justify-between mb-8 relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-0.5 bg-gray-100 z-0"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-brand-charcoal z-0 transition-all duration-500" :style="`width: ${((step - 1) / 2) * 100}%`"></div>
                    
                    <button type="button" @click="step = 1" class="relative z-10 flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-mono text-xs font-bold transition-colors border-2" :class="step >= 1 ? 'bg-brand-charcoal border-brand-charcoal text-white' : 'bg-white border-gray-200 text-brand-grey'">1</div>
                        <span class="font-mono text-[10px] uppercase tracking-wider hidden sm:block" :class="step >= 1 ? 'text-brand-charcoal font-bold' : 'text-brand-grey'">Details</span>
                    </button>
                    <button type="button" @click="step = 2" class="relative z-10 flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-mono text-xs font-bold transition-colors border-2" :class="step >= 2 ? 'bg-brand-charcoal border-brand-charcoal text-white' : 'bg-white border-gray-200 text-brand-grey'">2</div>
                        <span class="font-mono text-[10px] uppercase tracking-wider hidden sm:block" :class="step >= 2 ? 'text-brand-charcoal font-bold' : 'text-brand-grey'">Shipping</span>
                    </button>
                    <button type="button" @click="step = 3" class="relative z-10 flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-mono text-xs font-bold transition-colors border-2" :class="step >= 3 ? 'bg-brand-charcoal border-brand-charcoal text-white' : 'bg-white border-gray-200 text-brand-grey'">3</div>
                        <span class="font-mono text-[10px] uppercase tracking-wider hidden sm:block" :class="step >= 3 ? 'text-brand-charcoal font-bold' : 'text-brand-grey'">Payment</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
                    @csrf

                    <!-- STEP 1: Details -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-4">
                            Contact Details
                        </h2>
                        
                        <div class="space-y-5">
                            <div>
                                <label for="name" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                                    Full Name <span class="text-brand-red">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                                       class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-charcoal focus:ring-1 focus:ring-brand-charcoal outline-none shadow-sm transition-all @error('name') border-brand-red @enderror"
                                       placeholder="e.g. Ram Bahadur Shrestha">
                                @error('name')
                                    <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                                    Primary Phone Number <span class="text-brand-red">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required 
                                       class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-charcoal focus:ring-1 focus:ring-brand-charcoal outline-none shadow-sm transition-all @error('phone') border-brand-red @enderror"
                                       placeholder="e.g. 9841234567">
                                @error('phone')
                                    <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                                    Email Address (Optional)
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                                       class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-charcoal focus:ring-1 focus:ring-brand-charcoal outline-none shadow-sm transition-all @error('email') border-brand-red @enderror"
                                       placeholder="e.g. ram@example.com">
                            </div>

                            <button type="button" @click="step = 2" class="w-full bg-brand-charcoal hover:bg-black text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2 mt-8">
                                <span>Continue to Shipping</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Shipping -->
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-4">
                            Delivery Address
                        </h2>
                        
                        <div>
                            <label for="address" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                                Full Address <span class="text-brand-red">*</span>
                            </label>
                            <textarea id="address" name="address" rows="4" required 
                                      class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-charcoal focus:ring-1 focus:ring-brand-charcoal outline-none shadow-sm transition-all @error('address') border-brand-red @enderror"
                                      placeholder="e.g. House No. 42, New Road, Ward 22, Kathmandu">{{ old('address') }}</textarea>
                            <span class="font-mono text-[11px] text-brand-grey mt-1 block">
                                Include landmarks for faster delivery. Our courier will call to confirm before dispatch.
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-8">
                            <button type="button" @click="step = 1" class="w-full bg-gray-100 hover:bg-gray-200 text-brand-charcoal font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors">
                                Back
                            </button>
                            <button type="button" @click="step = 3" class="w-full bg-brand-charcoal hover:bg-black text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2">
                                <span>Continue to Payment</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Payment & Confirm -->
                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal mb-4">
                            Payment Method
                        </h2>
                        
                        <div class="border-2 border-brand-red bg-brand-red/5 p-5 relative overflow-hidden group hover:border-brand-red transition-all shadow-[0_8px_30px_rgba(220,38,38,0.1)]">
                            <div class="flex items-start space-x-3 relative z-10">
                                <div class="mt-0.5">
                                    <div class="h-4 w-4 rounded-full border-4 border-brand-red flex items-center justify-center"></div>
                                </div>
                                <div>
                                    <span class="font-mono text-sm font-bold uppercase text-brand-charcoal block">Cash on Delivery</span>
                                    <span class="font-sans text-sm text-brand-grey block mt-1">Pay in cash or eSewa/FonePay QR upon delivery. Secure and hassle-free.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Trust Badges -->
                        <div class="grid grid-cols-3 gap-4 mt-8 pt-8 border-t border-gray-100">
                            <div class="flex flex-col items-center text-center space-y-2">
                                <svg class="w-8 h-8 text-brand-charcoal opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="font-mono text-[10px] font-bold uppercase text-brand-charcoal">Secure Checkout</span>
                            </div>
                            <div class="flex flex-col items-center text-center space-y-2">
                                <svg class="w-8 h-8 text-brand-charcoal opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-mono text-[10px] font-bold uppercase text-brand-charcoal">Verified Quality</span>
                            </div>
                            <div class="flex flex-col items-center text-center space-y-2">
                                <svg class="w-8 h-8 text-brand-charcoal opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="font-mono text-[10px] font-bold uppercase text-brand-charcoal">Pay on Delivery</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-8">
                            <button type="button" @click="step = 2" class="w-full bg-gray-100 hover:bg-gray-200 text-brand-charcoal font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors">
                                Back
                            </button>
                            <button type="submit" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 shadow-[0_8px_30px_rgba(220,38,38,0.25)] hover:shadow-[0_8px_30px_rgba(220,38,38,0.4)] transition-all hover:scale-[1.02] flex items-center justify-center space-x-2">
                                <span>Place Order</span>
                            </button>
                        </div>
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
