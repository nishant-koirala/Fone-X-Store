@props(['condition'])

@php
    $product = $condition->product;
    $category = $product->category;
    $isNew = strtolower($condition->grade) === 'new';
    $savings = 0;
    if ($condition->original_price && $condition->original_price > $condition->price) {
        $savings = round((($condition->original_price - $condition->price) / $condition->original_price) * 100);
    }
@endphp

<a href="{{ route('products.show', $product->slug) }}" class="group block border border-gray-200 bg-white rounded-2xl p-5 hover:border-brand-red hover:shadow-xl transition-all duration-300 relative flex flex-col justify-between h-full">
    <!-- Condition & Stock Header -->
    <div class="flex items-center justify-between mb-4">
        @if($isNew)
            <x-badge color="red" outline>NEW</x-badge>
        @else
            <x-badge color="red">GRADE {{ strtoupper($condition->grade) }}</x-badge>
        @endif

        @if($condition->quantity_in_stock > 0)
            <span class="font-mono text-xs font-bold uppercase text-brand-charcoal tracking-wider">
                IN STOCK ({{ $condition->quantity_in_stock }})
            </span>
        @else
            <span class="font-mono text-xs font-bold uppercase text-brand-grey tracking-wider">
                OUT OF STOCK
            </span>
        @endif
    </div>

    <!-- Image Area -->
    <div class="aspect-[4/3] flex items-center justify-center bg-brand-offwhite p-6 relative overflow-hidden mb-5 border border-gray-100 rounded-xl">
        @if($product->image)
            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }} image" class="h-40 w-auto object-contain group-hover:scale-110 transition-transform duration-500 ease-out mix-blend-multiply" />
        @else
            <x-product-icon :categorySlug="$category->slug ?? ''" class="h-40 w-auto text-brand-charcoal/20 group-hover:text-brand-red/40 transition-colors duration-300 stroke-[1.5]" />
        @endif

        <span class="absolute bottom-2 left-2 font-mono text-xs uppercase tracking-widest text-brand-grey font-bold">
            {{ strtoupper($product->brand) }}
        </span>

        @if($savings > 0)
            <span class="absolute top-2 right-2 bg-brand-charcoal text-white font-mono text-xs font-bold px-1.5 py-0.5">
                SAVE {{ $savings }}%
            </span>
        @endif
    </div>

    <!-- Info Area -->
    <div class="space-y-1 flex-grow">
        <div class="font-mono text-xs text-brand-grey uppercase tracking-widest font-bold">
            {{ $category->name ?? 'SMARTPHONE' }}
        </div>
        <h3 class="font-sans font-bold text-brand-charcoal text-lg group-hover:text-brand-red transition-colors leading-snug block">
            {{ $product->name }}
        </h3>
        
        <!-- Ratings -->
        @if(isset($product->reviews_count) && $product->reviews_count > 0)
            <div class="flex items-center space-x-1 py-1">
                <div class="flex items-center text-brand-red">
                    @php $rating = round($product->reviews_avg_rating ?? 0); @endphp
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $rating)
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @else
                            <svg class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endif
                    @endfor
                </div>
                <span class="font-mono text-xs text-brand-grey font-bold">({{ $product->reviews_count }})</span>
            </div>
        @endif

    </div>

    <!-- Price Area (Bottom aligned) -->
    <div class="mt-6 pt-4 border-t border-gray-100 flex items-baseline justify-between">
        <div>
            <span class="font-mono text-xs text-brand-grey uppercase tracking-widest font-bold block mb-1">PRICE</span>
            <div class="flex items-baseline space-x-2">
                <span class="font-mono font-bold text-brand-charcoal text-xl">
                    Rs {{ number_format($condition->price) }}
                </span>
                @if($savings > 0)
                    <span class="font-mono text-xs text-brand-grey line-through font-bold">
                        Rs {{ number_format($condition->original_price) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="font-mono text-xs font-bold uppercase text-brand-red tracking-wider">VIEW &rarr;</span>
        </div>
    </div>
</a>
