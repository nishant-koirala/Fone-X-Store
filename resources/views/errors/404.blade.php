@extends('layouts.storefront')

@section('title', '404 — Page Not Found | FoneX Store')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-brand-offwhite px-4 py-16">
    <div class="max-w-xl w-full border-2 border-brand-charcoal bg-brand-white p-8 md:p-12 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)] text-center">
        <span class="inline-block border-2 border-brand-charcoal bg-brand-red text-brand-white px-4 py-1 font-mono text-xs font-bold uppercase tracking-widest mb-6 shadow-[2px_2px_0px_0px_rgba(42,42,44,1)]">
            Error 404
        </span>

        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-brand-charcoal mb-4">
            Device Lost In Transit
        </h1>

        <p class="font-sans text-brand-charcoal/80 text-base leading-relaxed mb-8">
            The page or product condition you are looking for has been sold out, moved, or never existed in our inventory database.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center border-2 border-brand-charcoal bg-brand-charcoal text-brand-white px-6 py-3 font-mono text-xs font-bold uppercase tracking-widest hover:bg-brand-red hover:border-brand-red transition-all shadow-[4px_4px_0px_0px_rgba(42,42,44,0.3)]">
                Back to Storefront
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center border-2 border-brand-charcoal bg-brand-white text-brand-charcoal px-6 py-3 font-mono text-xs font-bold uppercase tracking-widest hover:bg-brand-offwhite transition-all shadow-[4px_4px_0px_0px_rgba(42,42,44,1)]">
                Browse Phones
            </a>
        </div>
    </div>
</div>
@endsection
