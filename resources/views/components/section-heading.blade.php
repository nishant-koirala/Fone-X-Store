@props(['eyebrow', 'title', 'actionText' => null, 'actionUrl' => null])

<div class="flex flex-col md:flex-row md:items-end justify-between mb-10 pb-4 border-b-2 border-brand-charcoal gap-4">
    <div>
        @if($eyebrow)
            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red block mb-2">{{ $eyebrow }}</span>
        @endif
        <h2 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-brand-charcoal leading-none">
            {{ $title }}
        </h2>
    </div>
    @if($actionText && $actionUrl)
        <div>
            <a href="{{ $actionUrl }}" class="font-mono text-xs uppercase font-bold text-brand-charcoal hover:text-brand-red transition-colors flex items-center space-x-2 group">
                <span>{{ $actionText }}</span>
                <span class="transform group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>
    @endif
</div>
