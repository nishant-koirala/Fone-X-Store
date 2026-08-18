@extends('layouts.storefront')

@section('title', 'Your Wishlist - FoneX Store')

@section('content')
<div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="mb-10 text-center sm:text-left">
        <h1 class="font-display text-4xl uppercase tracking-tight text-brand-charcoal">Your Wishlist</h1>
        <p class="mt-2 font-mono text-sm text-brand-grey">Save your favorite devices for later.</p>
    </div>

    @if($wishlistProducts->isEmpty())
        <div class="bg-brand-offwhite p-12 text-center border border-gray-200">
            <svg class="w-16 h-16 text-brand-grey/50 mx-auto mb-4 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h2 class="font-sans font-bold text-lg text-brand-charcoal">Your wishlist is empty</h2>
            <p class="text-brand-grey text-sm mb-6 mt-1">Looks like you haven't added any phones to your wishlist yet.</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-brand-charcoal text-white font-mono text-xs font-bold uppercase tracking-wider px-6 py-3 hover:bg-brand-red transition-colors">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlistProducts as $product)
                @php $firstCond = $product->conditions->sortBy('price')->first(); @endphp
                @if($firstCond)
                    <div class="group card-glow-hover relative flex flex-col justify-between border border-gray-200 bg-white p-5">
                        
                        <!-- Remove from Wishlist Form -->
                        <form action="{{ route('wishlist.toggle', $product) }}" method="POST" class="absolute top-2 right-2 z-10">
                            @csrf
                            <button type="submit" class="p-2 text-brand-red hover:scale-110 transition-transform focus:outline-none bg-white/80 rounded-full" title="Remove from Wishlist">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>

                        <div class="flex items-center justify-between mb-4 mt-2">
                            <span class="border border-brand-red text-brand-red font-mono text-[10px] font-bold uppercase px-2 py-0.5">
                                {{ strtoupper($firstCond->grade) }}
                            </span>
                            <span class="font-mono text-[10px] text-brand-grey uppercase">
                                {{ $product->brand }}
                            </span>
                        </div>

                        <div class="shine-container aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-4 mb-4 border border-gray-100">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" class="h-full w-full object-contain group-hover:scale-110 transition-transform duration-300" />
                            @else
                                <x-product-icon :categorySlug="$product->category->slug ?? ''" class="h-20 w-20 text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors stroke-[1.5]" />
                            @endif
                        </div>

                        <div>
                            <a href="{{ route('products.show', $product->slug) }}" class="font-sans font-bold text-brand-charcoal text-base group-hover:text-brand-red transition-colors line-clamp-1">
                                {{ $product->name }}
                            </a>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-baseline justify-between">
                            <div class="flex items-baseline space-x-1.5">
                                <span class="font-mono font-bold text-brand-charcoal text-base">
                                    Rs {{ number_format($firstCond->price) }}
                                </span>
                            </div>
                            <a href="{{ route('products.show', $product->slug) }}" class="font-mono text-[11px] uppercase font-bold text-white bg-brand-charcoal group-hover:bg-brand-red px-3 py-1.5 transition-colors">
                                View
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
