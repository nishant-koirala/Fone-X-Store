@props(['color' => 'red', 'outline' => false])

@php
    $baseClasses = 'font-mono text-xs font-bold uppercase tracking-widest px-2 py-0.5 border-2';
    
    $colorClasses = match($color) {
        'charcoal' => $outline 
            ? 'border-brand-charcoal text-brand-charcoal bg-transparent'
            : 'border-brand-charcoal bg-brand-charcoal text-white',
        default => $outline 
            ? 'border-brand-red text-brand-red bg-transparent'
            : 'border-brand-red bg-brand-red text-white',
    };
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $colorClasses]) }}>
    {{ $slot }}
</span>
