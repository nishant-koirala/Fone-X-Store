@props(['categorySlug' => '', 'class' => 'h-24 w-24 text-brand-charcoal/20'])

@php
    $slug = strtolower($categorySlug);
@endphp

@if(str_contains($slug, 'audio') || str_contains($slug, 'speaker') || str_contains($slug, 'airpod') || str_contains($slug, 'headphone'))
    <!-- Audio / Speaker / AirPods Icon -->
    <svg class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
    </svg>
@elseif(str_contains($slug, 'charger') || str_contains($slug, 'cable') || str_contains($slug, 'power'))
    <!-- Charger / Plug / Power Bank Icon -->
    <svg class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
    </svg>
@elseif(str_contains($slug, 'case') || str_contains($slug, 'cover') || str_contains($slug, 'protector'))
    <!-- Case / Cover Shield Icon -->
    <svg class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>
@else
    <!-- Smartphone Outline Contour -->
    <svg class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
        <line x1="10" y1="4" x2="14" y2="4" stroke-linecap="round" />
        <circle cx="12" cy="19" r="0.75" fill="currentColor" />
    </svg>
@endif
