@extends('layouts.storefront')

@section('title', 'Order #' . $order->id . ' Confirmation — FoneX Store')

@section('content')

    <!-- Confirmation Header Banner -->
    <div class="bg-emerald-600 text-white py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/20 text-white mb-2">
                <svg class="h-10 w-10 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="square" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <span class="font-mono text-xs font-bold uppercase tracking-widest text-emerald-100 block">ORDER PLACED SUCCESSFULLY</span>
            <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight">
                Order #{{ $order->id }} Confirmed
            </h1>
            <p class="font-sans text-sm text-emerald-100 max-w-xl mx-auto">
                Thank you for shopping with FoneX Store! Your order has been registered and inventory has been reserved.
            </p>
        </div>
    </div>

    <!-- Main Order Details Container -->
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        
        <!-- Next Steps Notice -->
        <div class="border-2 border-brand-red bg-brand-offwhite p-6 sm:p-8 space-y-3">
            <div class="flex items-center space-x-2">
                <span class="h-3 w-3 bg-brand-red animate-pulse"></span>
                <h3 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">What Happens Next?</h3>
            </div>
            <p class="font-sans text-sm text-brand-charcoal leading-relaxed">
                Our customer service team will call you shortly at <span class="font-mono font-bold text-brand-red">{{ $order->customer->phone }}</span> to verify your order and arrange courier dispatch to <span class="font-semibold">{{ $order->customer->address }}</span>.
            </p>
        </div>

        <!-- Customer & Delivery Summary Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border border-gray-200 bg-white p-6 sm:p-8 font-sans text-sm">
            <div>
                <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-brand-grey mb-2">Customer Info</h4>
                <p class="font-bold text-brand-charcoal text-base">{{ $order->customer->name }}</p>
                <p class="font-mono text-xs text-brand-charcoal mt-1">Phone: {{ $order->customer->phone }}</p>
                @if($order->customer->email)
                    <p class="font-mono text-xs text-brand-grey">Email: {{ $order->customer->email }}</p>
                @endif
            </div>

            <div>
                <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-brand-grey mb-2">Delivery Address</h4>
                <p class="text-brand-charcoal leading-snug">{{ $order->customer->address }}</p>
                <div class="mt-2">
                    <span class="font-mono text-xs font-bold uppercase bg-amber-100 text-amber-800 px-2 py-0.5 border border-amber-300">
                        STATUS: {{ strtoupper($order->status) }} (CASH ON DELIVERY)
                    </span>
                </div>
            </div>
        </div>

        <!-- Purchased Order Items Table -->
        <div class="border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-brand-offwhite px-6 py-4 font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal flex justify-between">
                <span>Ordered Item Breakdown</span>
                <span>Line Total</span>
            </div>

            <div class="divide-y divide-gray-200 p-6 space-y-4">
                @foreach($order->items as $item)
                    @php 
                        $cond = $item->productCondition; 
                        $prod = $cond->product;
                    @endphp
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-[10px] font-bold uppercase bg-brand-red text-white px-1.5 py-0.5">
                                    GRADE {{ strtoupper($cond->grade) }}
                                </span>
                                <span class="font-sans font-bold text-brand-charcoal text-base">
                                    {{ $prod->name }}
                                </span>
                            </div>
                            <div class="font-mono text-xs text-brand-grey mt-1">
                                {{ $prod->brand }} • {{ $item->quantity }} × Rs {{ number_format($item->price_at_purchase) }}
                            </div>
                        </div>
                        <div class="font-mono font-bold text-brand-charcoal text-lg">
                            Rs {{ number_format($item->quantity * $item->price_at_purchase) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-200 bg-brand-offwhite p-6 flex items-baseline justify-between font-mono">
                <span class="font-bold text-sm text-brand-charcoal uppercase">Total Amount Payable</span>
                <span class="font-bold text-3xl text-brand-charcoal">Rs {{ number_format($order->total) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <a href="{{ route('home') }}" class="w-full sm:w-auto font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red px-8 py-4 transition-colors text-center">
                &larr; Return to Homepage
            </a>
            <a href="{{ route('products.index') }}" class="w-full sm:w-auto font-mono text-xs uppercase font-bold text-white bg-brand-red hover:bg-brand-red-dark px-8 py-4 transition-colors text-center">
                Browse More Phones
            </a>
        </div>

    </div>

@endsection
