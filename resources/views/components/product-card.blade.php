@props(['condition'])

@php
    $product = $condition->product;
    $category = $product->category;
    $gradeVal = $condition->grade instanceof \App\Enums\GradeEnum ? $condition->grade->value : (string) $condition->grade;
    $gradeLower = strtolower($gradeVal);
    $isNew = $gradeLower === 'new';
    $savings = 0;
    if ($condition->original_price && $condition->original_price > $condition->price) {
        $savings = round((($condition->original_price - $condition->price) / $condition->original_price) * 100);
    }

    $gradeBadges = [
        'new' => ['label' => 'Brand New Sealed', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300'],
        'a' => ['label' => 'Grade A • Pristine', 'bg' => 'bg-blue-50 text-blue-800 border-blue-300'],
        'b' => ['label' => 'Grade B • Excellent', 'bg' => 'bg-amber-50 text-amber-800 border-amber-300'],
        'c' => ['label' => 'Grade C • Fair Value', 'bg' => 'bg-stone-100 text-stone-800 border-stone-300'],
    ];
    $gradeMeta = $gradeBadges[$gradeLower] ?? ['label' => 'Grade ' . strtoupper($gradeVal), 'bg' => 'bg-gray-100 text-brand-charcoal border-gray-300'];
@endphp

<a href="{{ route('products.show', $product->slug) }}" 
   class="group flex flex-col justify-between h-full bg-brand-white border-2 border-brand-charcoal/20 hover:border-brand-charcoal rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-[6px_6px_0px_0px_rgba(42,42,44,1)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
    
    <div>
        <!-- Top Badges Row -->
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="inline-flex items-center font-mono text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border {{ $gradeMeta['bg'] }}">
                {{ $gradeMeta['label'] }}
            </span>

            @if($condition->quantity_in_stock > 0)
                <span class="inline-flex items-center space-x-1 font-mono text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>In Stock ({{ $condition->quantity_in_stock }})</span>
                </span>
            @else
                <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                    Sold Out
                </span>
            @endif
        </div>

        <!-- Product Image Stage -->
        <div class="aspect-[4/3] flex items-center justify-center bg-gradient-to-b from-brand-offwhite via-white to-brand-offwhite rounded-lg border border-brand-charcoal/10 p-5 relative overflow-hidden mb-4 group-hover:border-brand-charcoal/30 transition-colors">
            
            <!-- Subtle dot pattern -->
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#2A2A2C 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>

            @if($product->image)
                <img src="{{ Storage::url($product->image) }}" 
                     alt="{{ $product->name }}" 
                     class="max-h-36 sm:max-h-40 w-auto object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-500 ease-out" />
            @else
                <x-product-icon :categorySlug="$category->slug ?? ''" class="h-32 w-auto text-brand-charcoal/30 group-hover:scale-105 transition-transform duration-300 stroke-[1.2]" />
            @endif

            <!-- Floating Brand Tag -->
            <span class="absolute bottom-2 left-2 font-mono text-[10px] uppercase tracking-wider text-brand-charcoal font-bold bg-white/95 border border-brand-charcoal/20 px-2 py-0.5 rounded-sm shadow-xs">
                {{ $product->brand }}
            </span>

            <!-- Floating Savings Badge -->
            @if($savings > 0)
                <span class="absolute top-2 right-2 bg-brand-red text-white font-mono text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm shadow-xs">
                    SAVE {{ $savings }}%
                </span>
            @endif
        </div>

        <!-- Product Title & Details -->
        <div class="space-y-1">
            <div class="font-mono text-[10px] text-brand-grey uppercase tracking-wider font-bold">
                {{ $category->name ?? 'Smartphone' }}
            </div>
            
            <h3 class="font-display text-base uppercase tracking-tight text-brand-charcoal group-hover:text-brand-red transition-colors leading-snug line-clamp-1">
                {{ $product->name }}
            </h3>

            <!-- Rating or Guarantee Highlights -->
            <div class="flex items-center space-x-2 pt-0.5 text-[11px] font-sans text-brand-grey">
                @if(isset($product->reviews_count) && $product->reviews_count > 0)
                    <div class="flex items-center text-amber-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="font-mono font-bold text-brand-charcoal ml-1">{{ number_format($product->reviews_avg_rating ?? 5.0, 1) }}</span>
                    </div>
                    <span>•</span>
                @endif
                <span class="text-brand-charcoal/70">✓ 100-Point Inspected</span>
            </div>
        </div>
    </div>

    <!-- Pricing & View CTA (Bottom aligned) -->
    <div class="mt-4 pt-3.5 border-t border-brand-charcoal/15 flex items-center justify-between">
        <div>
            <div class="font-mono text-[10px] text-brand-grey uppercase tracking-wider font-bold">Price</div>
            <div class="flex items-baseline space-x-2">
                <span class="font-mono font-bold text-brand-red text-lg sm:text-xl">
                    Rs {{ number_format($condition->price) }}
                </span>
                @if($savings > 0)
                    <span class="font-mono text-xs text-brand-grey line-through">
                        Rs {{ number_format($condition->original_price) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="inline-flex items-center space-x-1 font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal group-hover:text-white bg-brand-offwhite group-hover:bg-brand-charcoal border border-brand-charcoal px-3 py-1.5 rounded transition-all shadow-[2px_2px_0px_0px_rgba(42,42,44,1)] group-hover:shadow-none">
            <span>View</span>
            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
        </div>
    </div>
</a>
