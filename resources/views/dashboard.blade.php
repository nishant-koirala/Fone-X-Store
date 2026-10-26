@extends('layouts.storefront')

@section('title', 'My Dashboard — FoneX Store')

@section('content')

    <!-- Stark Page Header -->
    <div class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-brand-white leading-none">
                Welcome, {{ auth()->user()->name }}
            </h1>
            <p class="mt-4 font-mono text-xs font-bold uppercase tracking-widest text-brand-white/70">
                Manage your orders and trade-ins
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-16 bg-brand-white min-h-[60vh]">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            
            <!-- Sidebar Navigation/Actions -->
            <div class="lg:col-span-3 space-y-8">
                <div class="border-2 border-brand-charcoal bg-brand-offwhite p-6 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <h3 class="font-mono text-sm font-bold uppercase tracking-widest text-brand-charcoal mb-4 pb-4 border-b-2 border-brand-charcoal">Account</h3>
                    <ul class="space-y-4">
                        <li>
                            <a href="{{ route('profile.edit') }}" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey hover:text-brand-red transition-colors flex items-center justify-between group">
                                <span>Profile Settings</span>
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey hover:text-brand-red transition-colors flex items-center justify-between group w-full text-left">
                                    <span>Log Out</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-9 space-y-16">
                
                <!-- Order History -->
                <section>
                    <div class="flex items-center space-x-4 mb-8">
                        <span class="flex items-center justify-center h-8 w-8 bg-brand-charcoal text-brand-white font-mono text-sm font-bold">1</span>
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Order History</h2>
                    </div>

                    @if($orders->count() > 0)
                        <div class="space-y-6">
                            @foreach($orders as $order)
                                <div class="border-2 border-brand-charcoal bg-brand-offwhite p-6 hover:shadow-[8px_8px_0px_0px_rgba(42,42,44,1)] transition-shadow">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b-2 border-brand-charcoal pb-4 mb-4 gap-4">
                                        <div>
                                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey block mb-1">Order ID</span>
                                            <span class="font-mono text-lg font-bold text-brand-charcoal">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey block mb-1">Date</span>
                                            <span class="font-mono font-bold text-brand-charcoal">{{ $order->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey block mb-1">Total</span>
                                            <span class="font-mono font-bold text-brand-red">Rs {{ number_format($order->total) }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey block mb-1">Status</span>
                                            <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-xs font-bold uppercase tracking-widest bg-brand-white {{ ($order->status instanceof \App\Enums\OrderStatusEnum ? $order->status === \App\Enums\OrderStatusEnum::PENDING : $order->status === 'pending') ? 'text-brand-red' : 'text-brand-charcoal' }}">
                                                {{ $order->status instanceof \App\Enums\OrderStatusEnum ? $order->status->getLabel() : $order->status }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        @foreach($order->items as $item)
                                            <div class="flex justify-between items-center text-sm font-mono font-bold">
                                                <span>{{ $item->quantity }}x {{ $item->productCondition->product->name ?? 'Unknown Product' }} (Grade {{ $item->productCondition?->grade instanceof \App\Enums\GradeEnum ? $item->productCondition->grade->value : ($item->productCondition->grade ?? 'N/A') }})</span>
                                                <span>Rs {{ number_format($item->price_at_purchase * $item->quantity) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border-2 border-dashed border-brand-charcoal p-12 text-center bg-brand-offwhite">
                            <p class="font-mono text-sm font-bold uppercase tracking-widest text-brand-grey">No orders found.</p>
                            <a href="{{ route('products.index') }}" class="inline-block mt-4 text-brand-red hover:text-brand-charcoal font-mono text-xs font-bold uppercase tracking-widest underline decoration-brand-red hover:decoration-brand-charcoal underline-offset-4 transition-colors">Start Shopping</a>
                        </div>
                    @endif
                </section>

                <!-- Trade-In Valuations -->
                <section>
                    <div class="flex items-center space-x-4 mb-8 border-t-2 border-brand-charcoal pt-16">
                        <span class="flex items-center justify-center h-8 w-8 bg-brand-charcoal text-brand-white font-mono text-sm font-bold">2</span>
                        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">Trade-In Valuations</h2>
                    </div>

                    @if($tradeIns->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($tradeIns as $tradeIn)
                                <div class="border-2 border-brand-charcoal bg-brand-white p-6 shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                                    <div class="flex justify-between items-start mb-4">
                                        <h4 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">{{ $tradeIn->device_brand }} {{ $tradeIn->device_model }}</h4>
                                        <span class="inline-block border border-brand-charcoal px-2 py-0.5 font-mono text-xs font-bold uppercase tracking-widest {{ ($tradeIn->status instanceof \App\Enums\TradeInStatusEnum ? $tradeIn->status === \App\Enums\TradeInStatusEnum::COMPLETED : $tradeIn->status === 'completed') ? 'bg-green-100 text-green-800' : 'bg-brand-offwhite text-brand-grey' }}">
                                            {{ $tradeIn->status instanceof \App\Enums\TradeInStatusEnum ? $tradeIn->status->getLabel() : $tradeIn->status }}
                                        </span>
                                    </div>
                                    <p class="font-sans text-sm text-brand-grey mb-6">{{ $tradeIn->condition_description }}</p>
                                    
                                    <div class="border-t-2 border-brand-charcoal pt-4 flex justify-between items-center">
                                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey">Estimated Value</span>
                                        @if($tradeIn->estimated_value)
                                            <span class="font-mono text-xl font-bold text-brand-red">Rs {{ number_format($tradeIn->estimated_value) }}</span>
                                        @else
                                            <span class="font-mono text-sm font-bold text-brand-charcoal uppercase">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border-2 border-dashed border-brand-charcoal p-12 text-center bg-brand-offwhite">
                            <p class="font-mono text-sm font-bold uppercase tracking-widest text-brand-grey">No trade-in requests found.</p>
                            <a href="{{ route('trade-in.create') }}" class="inline-block mt-4 text-brand-red hover:text-brand-charcoal font-mono text-xs font-bold uppercase tracking-widest underline decoration-brand-red hover:decoration-brand-charcoal underline-offset-4 transition-colors">Request Valuation</a>
                        </div>
                    @endif
                </section>

            </div>
        </div>

    </div>

@endsection
