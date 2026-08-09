@extends('layouts.storefront')

@section('title', 'Valuation Submitted — FoneX Store')

@section('content')

    <!-- Confirmation Banner -->
    <div class="bg-brand-red text-white py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/20 text-white mb-2">
                <svg class="h-10 w-10 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="square" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <span class="font-mono text-xs font-bold uppercase tracking-widest text-white/80 block">VALUATION REQUEST SUBMITTED</span>
            <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight">
                Device Assessment Pending
            </h1>
            <p class="font-sans text-sm text-white/90 max-w-xl mx-auto">
                Your device specifications have been received by our valuation specialists!
            </p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        
        <!-- 24-Hour Call Expectation Note -->
        <div class="border-2 border-brand-red bg-brand-offwhite p-6 sm:p-8 space-y-3">
            <div class="flex items-center space-x-2">
                <span class="h-3 w-3 bg-brand-red animate-pulse"></span>
                <h3 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">What Happens Next?</h3>
            </div>
            <p class="font-sans text-sm text-brand-charcoal leading-relaxed">
                We'll call you within <span class="font-mono font-bold text-brand-red">24 hours</span> at <span class="font-mono font-bold text-brand-charcoal">{{ $valuation->customer->phone }}</span> with a custom trade-in credit estimate for your <span class="font-bold">{{ $valuation->device_brand }} {{ $valuation->device_model }}</span>.
            </p>
        </div>

        <!-- Submitted Summary Card -->
        <div class="border border-gray-200 bg-white p-6 sm:p-8 space-y-6">
            <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal border-b border-gray-200 pb-3">
                Submitted Device Specifications
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-mono text-xs">
                <div>
                    <span class="text-brand-grey uppercase block">Device Brand & Model</span>
                    <span class="font-bold text-brand-charcoal text-base">{{ $valuation->device_brand }} {{ $valuation->device_model }}</span>
                </div>
                <div>
                    <span class="text-brand-grey uppercase block">Customer Contact Name</span>
                    <span class="font-bold text-brand-charcoal text-base">{{ $valuation->customer->name }}</span>
                </div>
                <div>
                    <span class="text-brand-grey uppercase block">Primary Contact Phone</span>
                    <span class="font-bold text-brand-charcoal text-base">{{ $valuation->customer->phone }}</span>
                </div>
                <div>
                    <span class="text-brand-grey uppercase block">Assessment Status</span>
                    <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 border border-amber-300 inline-block mt-0.5">
                        PENDING REVIEW
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <span class="font-mono text-xs text-brand-grey uppercase block mb-1">Diagnostic Details</span>
                <p class="font-sans text-sm text-brand-charcoal bg-brand-offwhite p-3 border border-gray-200">
                    {{ $valuation->condition_description }}
                </p>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <a href="{{ route('home') }}" class="w-full sm:w-auto font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red px-8 py-4 transition-colors text-center">
                &larr; Return to Homepage
            </a>
            <a href="{{ route('products.index') }}" class="w-full sm:w-auto font-mono text-xs uppercase font-bold text-white bg-brand-red hover:bg-brand-red-dark px-8 py-4 transition-colors text-center">
                Browse Phones Inventory
            </a>
        </div>

    </div>

@endsection
