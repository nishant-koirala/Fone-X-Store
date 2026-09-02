@extends('layouts.storefront')

@section('title', 'Register — FoneX Store')

@section('content')
    <div class="bg-brand-white min-h-[70vh] flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-md w-full border-2 border-brand-charcoal bg-brand-offwhite p-8 sm:p-12 shadow-[12px_12px_0px_0px_rgba(42,42,44,1)]">
            
            <div class="text-center mb-10 pb-6 border-b-2 border-brand-charcoal">
                <h1 class="font-display text-4xl uppercase tracking-tight text-brand-charcoal">Register</h1>
                <p class="mt-2 font-mono text-xs uppercase tracking-widest text-brand-grey font-bold">Create a new account</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                        class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                        class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
                </div>

                <div class="pt-6 mt-6 border-t-2 border-brand-charcoal">
                    <button type="submit" class="w-full bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-8 py-5 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
                        Register &rarr;
                    </button>
                </div>
                
                <div class="text-center mt-6">
                    <p class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-brand-charcoal hover:text-brand-red underline decoration-brand-charcoal hover:decoration-brand-red underline-offset-4 transition-colors">Log In</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection
