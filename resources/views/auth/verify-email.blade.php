@extends('layouts.storefront')

@section('title', 'Verify Email — FoneX Store')

@section('content')
    <div class="bg-brand-white min-h-[70vh] flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-md w-full border-2 border-brand-charcoal bg-brand-offwhite p-8 sm:p-12 shadow-[12px_12px_0px_0px_rgba(42,42,44,1)]">
            
            <div class="text-center mb-10 pb-6 border-b-2 border-brand-charcoal">
                <h1 class="font-display text-4xl uppercase tracking-tight text-brand-charcoal">Verify Email</h1>
            </div>

            <div class="mb-8 font-sans text-sm text-brand-charcoal/80 text-center">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-8 font-mono text-xs font-bold text-green-600 text-center border-2 border-green-600 p-4 bg-green-50">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-8 py-5 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-brand-grey hover:text-brand-charcoal font-mono text-xs font-bold uppercase tracking-widest py-4 underline decoration-brand-grey hover:decoration-brand-charcoal underline-offset-4 transition-colors">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
