@extends('layouts.storefront')

@section('title', 'Trade-In Valuation — FoneX Store')

@section('content')

    <!-- Stark Page Header -->
    <div class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12 lg:py-24">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block border-2 border-brand-charcoal px-3 py-1 font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal bg-brand-white shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] mb-6">
                INSTANT VALUATION
            </span>
            <h1 class="font-display text-5xl sm:text-7xl uppercase tracking-tight text-brand-white leading-none">
                Trade & Upgrade
            </h1>
            <p class="mt-6 font-sans text-xl text-brand-white/80 max-w-2xl mx-auto font-light">
                Turn your old device into store credit. Answer a few questions about your phone to request an instant quote.
            </p>
        </div>
    </div>

    <!-- Trade-In Wizard (Alpine.js) -->
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-16" x-data="{ step: 1, totalSteps: 3 }">
        
        <!-- Step Progress Indicator -->
        <div class="flex items-center justify-between mb-12 relative">
            <div class="absolute left-0 top-1/2 w-full h-0.5 bg-gray-200 -z-10 transform -translate-y-1/2"></div>
            
            <template x-for="i in totalSteps" :key="i">
                <div class="flex flex-col items-center bg-brand-offwhite px-4">
                    <div class="h-10 w-10 flex items-center justify-center border-2 font-mono text-sm font-bold transition-colors duration-300"
                        :class="step >= i ? 'border-brand-charcoal bg-brand-charcoal text-brand-white' : 'border-gray-300 bg-brand-white text-gray-400'">
                        <span x-text="i"></span>
                    </div>
                    <span class="font-mono text-xs uppercase font-bold mt-2 tracking-widest"
                        :class="step >= i ? 'text-brand-charcoal' : 'text-gray-400'"
                        x-text="i === 1 ? 'Device' : (i === 2 ? 'Condition' : 'Contact')"></span>
                </div>
            </template>
        </div>

        <div class="border-2 border-brand-charcoal bg-brand-white p-8 sm:p-12 shadow-[12px_12px_0px_0px_rgba(42,42,44,1)]">
            
            <!-- Single Form Wrapper -->
            <form action="{{ route('trade-in.store') }}" method="POST">
                @csrf

                <!-- STEP 1: Device Details -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms>
                    <h2 class="font-display text-3xl uppercase tracking-tight text-brand-charcoal mb-8 pb-4 border-b-2 border-brand-charcoal">What are you trading in?</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="device_brand" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Device Brand</label>
                            <input type="text" id="device_brand" name="device_brand" required placeholder="e.g. Apple, Samsung"
                                class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-4 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                            @error('device_brand') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="device_model" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Device Model & Storage</label>
                            <input type="text" id="device_model" name="device_model" required placeholder="e.g. iPhone 13 Pro 256GB"
                                class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-4 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                            @error('device_model') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end">
                        <button type="button" @click="step = 2" class="bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-8 py-4 transition-colors flex items-center space-x-2">
                            <span>Next Step</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Condition Assessment -->
                <div x-show="step === 2" style="display: none;" x-transition.opacity.duration.300ms>
                    <h2 class="font-display text-3xl uppercase tracking-tight text-brand-charcoal mb-8 pb-4 border-b-2 border-brand-charcoal">Assess Condition</h2>
                    
                    <div>
                        <label class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-4">Select the best match for your device</label>
                        
                        <div class="space-y-4">
                            <!-- Option Flawless -->
                            <label class="flex items-start p-4 border-2 border-brand-charcoal bg-brand-offwhite cursor-pointer hover:border-brand-red transition-colors has-[:checked]:border-brand-red has-[:checked]:bg-brand-red/5">
                                <div class="flex items-center h-6">
                                    <input type="radio" name="condition" value="flawless" class="h-5 w-5 text-brand-red border-brand-charcoal focus:ring-brand-red">
                                </div>
                                <div class="ml-4">
                                    <span class="font-mono font-bold uppercase text-brand-charcoal block">Flawless</span>
                                    <span class="font-sans text-sm text-brand-charcoal/70 block mt-1">Looks brand new, zero scratches, battery health > 90%.</span>
                                </div>
                            </label>

                            <!-- Option Good -->
                            <label class="flex items-start p-4 border-2 border-brand-charcoal bg-brand-offwhite cursor-pointer hover:border-brand-red transition-colors has-[:checked]:border-brand-red has-[:checked]:bg-brand-red/5">
                                <div class="flex items-center h-6">
                                    <input type="radio" name="condition" value="good" checked class="h-5 w-5 text-brand-red border-brand-charcoal focus:ring-brand-red">
                                </div>
                                <div class="ml-4">
                                    <span class="font-mono font-bold uppercase text-brand-charcoal block">Good (Normal Wear)</span>
                                    <span class="font-sans text-sm text-brand-charcoal/70 block mt-1">Minor scratches on body/screen, no cracks, fully functional.</span>
                                </div>
                            </label>

                            <!-- Option Cracked/Broken -->
                            <label class="flex items-start p-4 border-2 border-brand-charcoal bg-brand-offwhite cursor-pointer hover:border-brand-red transition-colors has-[:checked]:border-brand-red has-[:checked]:bg-brand-red/5">
                                <div class="flex items-center h-6">
                                    <input type="radio" name="condition" value="broken" class="h-5 w-5 text-brand-red border-brand-charcoal focus:ring-brand-red">
                                </div>
                                <div class="ml-4">
                                    <span class="font-mono font-bold uppercase text-brand-charcoal block">Cracked / Requires Repair</span>
                                    <span class="font-sans text-sm text-brand-charcoal/70 block mt-1">Cracked screen, broken back glass, or non-functional parts.</span>
                                </div>
                            </label>
                        </div>
                        @error('condition') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-12 flex justify-between">
                        <button type="button" @click="step = 1" class="text-brand-grey hover:text-brand-charcoal font-mono text-xs uppercase font-bold tracking-widest px-4 py-4 transition-colors">
                            &larr; Back
                        </button>
                        <button type="button" @click="step = 3" class="bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-8 py-4 transition-colors flex items-center space-x-2">
                            <span>Next Step</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Contact Details -->
                <div x-show="step === 3" style="display: none;" x-transition.opacity.duration.300ms>
                    <h2 class="font-display text-3xl uppercase tracking-tight text-brand-charcoal mb-8 pb-4 border-b-2 border-brand-charcoal">Your Contact Info</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="customer_name" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Full Name</label>
                            <input type="text" id="customer_name" name="customer_name" required
                                class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-4 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                            @error('customer_name') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Phone Number</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required
                                class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-4 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                            @error('customer_phone') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="additional_notes" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Additional Notes (Optional)</label>
                            <textarea id="additional_notes" name="additional_notes" rows="3" placeholder="Any other details we should know?"
                                class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-4 font-mono font-bold text-sm focus:border-brand-red focus:ring-0 resize-none"></textarea>
                            @error('additional_notes') <p class="text-brand-red font-mono text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-12 flex justify-between items-center pt-8 border-t-2 border-brand-charcoal">
                        <button type="button" @click="step = 2" class="text-brand-grey hover:text-brand-charcoal font-mono text-xs uppercase font-bold tracking-widest px-4 py-4 transition-colors">
                            &larr; Back
                        </button>
                        <button type="submit" class="bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-10 py-5 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
                            Submit Request
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

@endsection
