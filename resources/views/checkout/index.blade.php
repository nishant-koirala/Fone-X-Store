@extends('layouts.storefront')

@section('title', 'Secure Checkout — FoneX Store')
@section('meta_description', 'Complete your purchase securely at Fone-X-Store.')
@section('meta_keywords', 'checkout, secure checkout, payment, fonex store')

@section('content')

    <!-- Stark Page Header -->
    <div class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-brand-white leading-none">
                Checkout
            </h1>
        </div>
    </div>

    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-16 bg-brand-white">
        
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                
                <!-- Left: Checkout Forms -->
                <div class="lg:col-span-7 space-y-12">
                    
                    <!-- Customer Details -->
                    <div class="border-2 border-brand-charcoal bg-brand-offwhite p-8">
                        <div class="flex items-center space-x-4 mb-8 border-b-2 border-brand-charcoal pb-4">
                            <span class="flex items-center justify-center h-8 w-8 bg-brand-charcoal text-brand-white font-mono text-sm font-bold">2</span>
                            <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Delivery Details</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="name" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Full Name</label>
                                <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                    class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                                @error('name') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Email Address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                                @error('email') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="phone" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required value="{{ old('phone') }}"
                                    class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                                @error('phone') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Full Shipping Address (Nepal)</label>
                                <textarea id="address" name="address" required rows="3"
                                    class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0 resize-none">{{ old('address') }}</textarea>
                                @error('address') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="border-2 border-brand-charcoal bg-brand-offwhite p-8">
                        <div class="flex items-center space-x-4 mb-8 border-b-2 border-brand-charcoal pb-4">
                            <span class="flex items-center justify-center h-8 w-8 bg-brand-charcoal text-brand-white font-mono text-sm font-bold">3</span>
                            <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Payment Method</h2>
                        </div>

                        <!-- Only COD available for now based on requirements -->
                        <div class="border-2 border-brand-charcoal bg-brand-white p-6 flex items-start space-x-4">
                            <div class="flex items-center h-6">
                                <input type="radio" id="payment_cod" name="payment_method" value="cod" checked class="h-5 w-5 text-brand-red border-brand-charcoal focus:ring-brand-red">
                            </div>
                            <div>
                                <label for="payment_cod" class="font-mono font-bold uppercase tracking-widest text-brand-charcoal block cursor-pointer">Cash on Delivery (COD)</label>
                                <p class="font-sans text-sm text-brand-charcoal/70 mt-1">Pay with cash when your order is delivered to your address.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right: Order Summary (Sticky) -->
                <div class="lg:col-span-5">
                    <div class="sticky top-28 bg-brand-charcoal text-brand-white border-2 border-brand-charcoal p-8 shadow-[8px_8px_0px_0px_rgba(200,32,45,1)]">
                        <h3 class="font-display text-2xl uppercase tracking-tight mb-6 pb-4 border-b-2 border-brand-white/20">Final Review</h3>
                        
                        <!-- Items list -->
                        <div class="space-y-4 mb-8">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-grow">
                                        <div class="font-mono text-xs font-bold">{{ $item['condition']->product->name }}</div>
                                        <div class="font-mono text-xs text-brand-white/50 uppercase">
                                            Cond: {{ $item['condition']->grade instanceof \App\Enums\GradeEnum ? $item['condition']->grade->value : $item['condition']->grade }} • Qty: {{ $item['quantity'] }}
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold flex-shrink-0">
                                        Rs {{ number_format($item['condition']->price * $item['quantity']) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-4 font-mono text-sm font-bold border-t-2 border-brand-white/20 pt-6">
                            <div class="flex justify-between">
                                <span class="text-brand-white/70">Subtotal</span>
                                <span>Rs {{ number_format($total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-brand-white/70">Shipping</span>
                                <span class="text-brand-red uppercase">Free</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t-2 border-brand-white/20 flex justify-between items-end">
                            <span class="font-mono text-sm font-bold uppercase tracking-widest">Total to Pay</span>
                            <span class="font-mono text-3xl font-bold text-brand-red">Rs {{ number_format($total) }}</span>
                        </div>

                        <div class="mt-10">
                            <button type="submit" class="block w-full text-center bg-brand-red hover:bg-brand-white hover:text-brand-charcoal text-brand-white font-mono text-sm uppercase tracking-widest font-bold px-6 py-6 transition-colors duration-300">
                                Confirm Order &rarr;
                            </button>
                        </div>
                        
                    </div>
                </div>

            </div>
        </form>

    </div>

@endsection
