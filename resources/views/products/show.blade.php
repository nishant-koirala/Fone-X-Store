@extends('layouts.storefront')

@section('title', $product->name . ' — FoneX Store')

@section('content')

@push('head')
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": "{{ $product->image ? url(Storage::url($product->image)) : asset('images/default-og.jpg') }}",
  "description": "{{ Str::limit(strip_tags($product->description), 150) }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $product->brand }}"
  },
  "offers": {
    "@type": "AggregateOffer",
    "url": "{{ url()->current() }}",
    "priceCurrency": "NPR",
    "lowPrice": "{{ $product->conditions->min('price') ?? 0 }}",
    "highPrice": "{{ $product->conditions->max('price') ?? 0 }}",
    "offerCount": "{{ $product->conditions->count() }}"
  }
}
</script>
@endpush
<!-- Alpine State for the single product page -->
<div x-data="productDetail({ conditions: {{ \Illuminate\Support\Js::from($product->conditions) }} })" class="bg-brand-white">

    <!-- Breadcrumb (Minimal) -->
    <div class="border-b-2 border-brand-charcoal py-4 bg-brand-white">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <nav class="flex font-mono text-xs font-bold uppercase tracking-widest text-brand-grey space-x-2">
                <a href="{{ route('home') }}" class="hover:text-brand-charcoal transition-colors">Home</a>
                <span>/</span>
                <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}" class="hover:text-brand-charcoal transition-colors">
                    {{ $product->category->name ?? 'Category' }}
                </a>
                <span>/</span>
                <span class="text-brand-charcoal">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Top Section (Split) -->
    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24">
            
            <!-- Left: Image Gallery (Stark Background) -->
            <div class="bg-brand-offwhite border-2 border-brand-charcoal p-12 flex items-center justify-center relative shadow-[12px_12px_0px_0px_rgba(42,42,44,1)] min-h-[500px]">
                
                <!-- Decorative grid background -->
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#2A2A2C 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
                
                <div class="relative z-10 w-full flex justify-center">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="max-h-[500px] w-auto object-contain mix-blend-multiply hover:scale-105 transition-transform duration-500 ease-out drop-shadow-xl" />
                    @else
                        <x-product-icon :categorySlug="$product->category->slug ?? ''" class="h-64 text-brand-charcoal stroke-[1]" />
                    @endif
                </div>

                <!-- Brand tag -->
                <div class="absolute bottom-6 left-6">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white px-3 py-1 border-2 border-brand-charcoal shadow-[2px_2px_0px_0px_rgba(200,32,45,1)]">
                        {{ $product->brand }}
                    </span>
                </div>
            </div>

            <!-- Right: Product Info & Actions -->
            <div class="flex flex-col">
                
                <div class="mb-8">
                    <h1 class="font-display text-5xl lg:text-7xl uppercase tracking-tight text-brand-charcoal leading-[0.9]">
                        {{ $product->name }}
                    </h1>
                    
                    @if(isset($product->reviews_count) && $product->reviews_count > 0)
                        <div class="flex items-center space-x-4 mt-6">
                            <div class="flex items-center text-brand-red">
                                @php $rating = round($product->reviews_avg_rating ?? 0); @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $rating)
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 text-brand-grey fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="font-mono text-xs font-bold text-brand-grey uppercase tracking-widest">
                                {{ $product->reviews_count }} Verified Reviews
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Price Display -->
                <div class="mb-10 p-6 border-2 border-brand-charcoal bg-brand-offwhite">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Selected Configuration Price</span>
                    <div class="flex items-end space-x-4">
                        <span class="font-mono text-4xl sm:text-5xl font-bold text-brand-red" x-text="'Rs ' + Number(selectedCondition.price).toLocaleString()"></span>
                        
                        <template x-if="savings > 0">
                            <div class="flex flex-col pb-1">
                                <span class="font-mono text-lg text-brand-grey line-through font-bold" x-text="'Rs ' + Number(selectedCondition.original_price).toLocaleString()"></span>
                                <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white border border-brand-charcoal px-2 py-0.5 inline-block mt-1">
                                    SAVE <span x-text="savings + '%'"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Condition Selector (Bento Style) -->
                <div class="mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">Select Condition</span>
                        <a href="#" class="font-mono text-xs uppercase font-bold text-brand-grey hover:text-brand-red underline decoration-brand-grey underline-offset-4">Condition Guide</a>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="c in conditions" :key="c.id">
                            <button type="button" @click="selectCondition(c)" 
                                :aria-pressed="selectedCondition.id === c.id"
                                :class="{
                                    'border-brand-red bg-brand-white shadow-[4px_4px_0px_0px_rgba(200,32,45,1)]': selectedCondition.id === c.id,
                                    'border-brand-charcoal bg-brand-offwhite hover:bg-brand-white hover:border-brand-red opacity-60': selectedCondition.id !== c.id,
                                    'opacity-30 cursor-not-allowed': c.quantity_in_stock <= 0
                                }"
                                class="text-left border-2 p-4 transition-all duration-200">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-display text-xl uppercase tracking-tight text-brand-charcoal" x-text="c.grade.toUpperCase() === 'NEW' ? 'BRAND NEW' : 'GRADE ' + c.grade.toUpperCase()"></span>
                                    <template x-if="c.quantity_in_stock > 0">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 mt-1.5"></span>
                                    </template>
                                </div>
                                <div class="font-mono text-xs font-bold text-brand-charcoal mt-1" x-text="'Rs ' + Number(c.price).toLocaleString()"></div>
                                <div class="font-mono text-xs uppercase text-brand-grey mt-2 tracking-widest" x-text="c.quantity_in_stock > 0 ? c.quantity_in_stock + ' IN STOCK' : 'OUT OF STOCK'"></div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                    @csrf
                    <input type="hidden" name="condition_id" :value="selectedCondition.id">
                    
                    <div class="flex gap-4">
                        <div class="w-24">
                            <label for="quantity" class="sr-only">Quantity</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" :max="selectedCondition.quantity_in_stock" 
                                class="w-full h-16 bg-brand-white border-2 border-brand-charcoal text-center font-mono text-lg font-bold focus:border-brand-red focus:ring-0 rounded-none">
                        </div>
                        
                        <button type="submit" 
                            :disabled="selectedCondition.quantity_in_stock <= 0"
                            class="flex-grow h-16 bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm sm:text-base uppercase font-bold tracking-widest transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-3">
                            <span x-text="selectedCondition.quantity_in_stock > 0 ? 'Add to Cart' : 'Out of Stock'"></span>
                            <template x-if="selectedCondition.quantity_in_stock > 0">
                                <span>&rarr;</span>
                            </template>
                        </button>
                    </div>
                </form>

                <!-- Value Props (Minimal Icons) -->
                <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t-2 border-brand-charcoal">
                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 flex-shrink-0 text-brand-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">Verified Quality Guarantee</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 flex-shrink-0 text-brand-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal">7-Day Checking Warranty</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Details Accordion Section -->
    <div class="border-t-2 border-brand-charcoal bg-brand-white">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-24">
            
            <div class="max-w-4xl mx-auto">
                <x-section-heading title="Device Specifications" />

                <!-- Description content (Rich text from admin) -->
                <div class="prose prose-lg prose-headings:font-display prose-headings:uppercase prose-p:font-sans prose-p:font-light prose-a:text-brand-red max-w-none text-brand-charcoal mt-12 mb-16">
                    {!! $product->description !!}
                </div>
                
                @if(isset($product->specifications) && is_array($product->specifications) && count($product->specifications) > 0)
                    <!-- Specs Accordion (Alpine) -->
                    <div x-data="{ active: null }" class="border-t-2 border-brand-charcoal">
                        @foreach($product->specifications as $key => $value)
                            <div class="border-b-2 border-brand-charcoal">
                                <button @click="active !== {{ $loop->index }} ? active = {{ $loop->index }} : active = null" class="w-full flex items-center justify-between py-6 text-left group">
                                    <span class="font-mono text-sm font-bold uppercase tracking-widest text-brand-charcoal group-hover:text-brand-red transition-colors">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="transform transition-transform duration-300" :class="active === {{ $loop->index }} ? 'rotate-180' : ''">
                                        <svg class="w-6 h-6 text-brand-charcoal group-hover:text-brand-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="square" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </button>
                                <div x-show="active === {{ $loop->index }}" x-collapse>
                                    <div class="pb-6 font-sans text-lg text-brand-charcoal/80 font-light">
                                        {{ is_array($value) ? implode(', ', $value) : $value }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productDetail', (initialData) => ({
            conditions: initialData.conditions,
            selectedCondition: null,

            init() {
                // Find a condition with stock, or default to the first one
                let inStock = this.conditions.find(c => c.quantity_in_stock > 0);
                this.selectedCondition = inStock ? inStock : this.conditions[0];
            },
            
            selectCondition(c) {
                this.selectedCondition = c;
            },

            get savings() {
                if (!this.selectedCondition.original_price || this.selectedCondition.original_price <= this.selectedCondition.price) {
                    return 0;
                }
                return Math.round(((this.selectedCondition.original_price - this.selectedCondition.price) / this.selectedCondition.original_price) * 100);
            }
        }));
    });
</script>
@endsection
