@extends('layouts.storefront')

@section('title', 'Trade-In Valuation — FoneX Store')

@section('content')

    <!-- Trade-In Header Banner -->
    <div class="bg-brand-offwhite border-b border-gray-200 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <span class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red">EXCHANGE & UPGRADE</span>
            <h1 class="font-display text-3xl sm:text-5xl uppercase tracking-tight text-brand-charcoal mt-1">
                Trade-In Valuation Request
            </h1>
            <p class="font-sans text-sm text-brand-grey max-w-2xl mt-2">
                Turn your old smartphone into instant store credit. Answer a few quick condition questions below and our valuation team will contact you within 24 hours with an estimate.
            </p>
        </div>
    </div>

    <!-- Main Form Container -->
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
        <form method="POST" action="{{ route('trade-in.store') }}" class="border border-gray-200 bg-white p-6 sm:p-10 space-y-8">
            @csrf

            <!-- Section 1: Device Identification -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2 border-b border-gray-200 pb-3">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest bg-brand-red text-white px-2 py-0.5">STEP 01</span>
                    <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">Device Information</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Brand Select -->
                    <div>
                        <label for="device_brand" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Device Brand <span class="text-brand-red">*</span>
                        </label>
                        <select id="device_brand" name="device_brand" required
                                class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('device_brand') border-brand-red @enderror">
                            <option value="">Select Brand...</option>
                            <option value="Apple" {{ old('device_brand') == 'Apple' ? 'selected' : '' }}>Apple (iPhone)</option>
                            <option value="Samsung" {{ old('device_brand') == 'Samsung' ? 'selected' : '' }}>Samsung</option>
                            <option value="Google" {{ old('device_brand') == 'Google' ? 'selected' : '' }}>Google (Pixel)</option>
                            <option value="OnePlus" {{ old('device_brand') == 'OnePlus' ? 'selected' : '' }}>OnePlus</option>
                            <option value="Xiaomi" {{ old('device_brand') == 'Xiaomi' ? 'selected' : '' }}>Xiaomi / Redmi</option>
                            <option value="Other" {{ old('device_brand') == 'Other' ? 'selected' : '' }}>Other Brand</option>
                        </select>
                        @error('device_brand')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Model Text Input -->
                    <div>
                        <label for="device_model" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Device Model & Storage <span class="text-brand-red">*</span>
                        </label>
                        <input type="text" id="device_model" name="device_model" value="{{ old('device_model') }}" required
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('device_model') border-brand-red @enderror"
                               placeholder="e.g. iPhone 13 Pro 128GB">
                        @error('device_model')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Specific Condition Questions -->
            <div class="space-y-6 pt-4">
                <div class="flex items-center space-x-2 border-b border-gray-200 pb-3">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest bg-brand-red text-white px-2 py-0.5">STEP 02</span>
                    <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">Diagnostic Questionnaire</h2>
                </div>

                <!-- Screen Condition -->
                <div class="space-y-2">
                    <label class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block">
                        Screen Glass & Display Condition <span class="text-brand-red">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="screen_condition" value="Flawless (No Scratches)" {{ old('screen_condition') == 'Flawless (No Scratches)' ? 'checked' : '' }} required class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Flawless (No Scratches)</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="screen_condition" value="Minor Scratches" {{ old('screen_condition') == 'Minor Scratches' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Minor Scratches</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="screen_condition" value="Cracked Screen / Display Damage" {{ old('screen_condition') == 'Cracked Screen / Display Damage' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Cracked / Damaged</span>
                        </label>
                    </div>
                </div>

                <!-- Battery Health -->
                <div class="space-y-2">
                    <label class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block">
                        Battery Health Capacity <span class="text-brand-red">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="battery_health" value="85%+ (Excellent)" {{ old('battery_health') == '85%+ (Excellent)' ? 'checked' : '' }} required class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">85%+ (Excellent)</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="battery_health" value="75-84% (Good)" {{ old('battery_health') == '75-84% (Good)' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">75–84% (Good)</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="battery_health" value="Below 75% (Degraded)" {{ old('battery_health') == 'Below 75% (Degraded)' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Below 75%</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="battery_health" value="Unknown" {{ old('battery_health') == 'Unknown' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Unknown</span>
                        </label>
                    </div>
                </div>

                <!-- Body Condition -->
                <div class="space-y-2">
                    <label class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block">
                        Physical Body & Frame Damage <span class="text-brand-red">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="physical_condition" value="Like New (No dents)" {{ old('physical_condition') == 'Like New (No dents)' ? 'checked' : '' }} required class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Like New (No dents)</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="physical_condition" value="Minor Scuffs & Wear" {{ old('physical_condition') == 'Minor Scuffs & Wear' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Minor Scuffs / Wear</span>
                        </label>
                        <label class="border-2 border-gray-200 p-3 hover:border-brand-red transition-colors cursor-pointer flex items-center space-x-2 bg-brand-offwhite/50">
                            <input type="radio" name="physical_condition" value="Heavy Dents or Back Glass Crack" {{ old('physical_condition') == 'Heavy Dents or Back Glass Crack' ? 'checked' : '' }} class="text-brand-red focus:ring-brand-red">
                            <span class="font-sans text-xs text-brand-charcoal font-semibold">Heavy Dents / Cracks</span>
                        </label>
                    </div>
                </div>

                <!-- Additional Notes / Included Accessories -->
                <div>
                    <label for="notes" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                        Additional Notes / Included Accessories (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="2" 
                              class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0"
                              placeholder="e.g. Includes original box and fast charger cable">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Section 3: Contact Details -->
            <div class="space-y-4 pt-4">
                <div class="flex items-center space-x-2 border-b border-gray-200 pb-3">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest bg-brand-red text-white px-2 py-0.5">STEP 03</span>
                    <h2 class="font-display text-xl uppercase tracking-tight text-brand-charcoal">Your Contact Info</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_name" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Full Name <span class="text-brand-red">*</span>
                        </label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('customer_name') border-brand-red @enderror"
                               placeholder="e.g. Bikash Thapa">
                        @error('customer_name')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="font-mono text-xs font-bold uppercase tracking-wider text-brand-charcoal block mb-1.5">
                            Primary Phone Number (Required) <span class="text-brand-red">*</span>
                        </label>
                        <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required
                               class="w-full font-sans text-sm border border-gray-300 bg-white text-brand-charcoal p-3 focus:border-brand-red focus:ring-0 @error('customer_phone') border-brand-red @enderror"
                               placeholder="e.g. 9812345678">
                        @error('customer_phone')
                            <p class="font-mono text-xs text-brand-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <div class="pt-6 border-t border-gray-200">
                <button type="submit" class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-mono text-xs uppercase font-bold tracking-wider py-4 transition-colors flex items-center justify-center space-x-2">
                    <span>Submit Device for Trade-In Valuation</span>
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

        </form>
    </div>

@endsection
