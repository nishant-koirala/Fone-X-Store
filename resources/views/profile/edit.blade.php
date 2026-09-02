@extends('layouts.storefront')

@section('title', 'Profile Settings — FoneX Store')

@section('content')
    <!-- Stark Page Header -->
    <div class="bg-brand-charcoal text-brand-white border-b border-brand-charcoal py-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl sm:text-6xl uppercase tracking-tight text-brand-white leading-none">
                Profile Settings
            </h1>
            <p class="mt-4 font-mono text-xs font-bold uppercase tracking-widest text-brand-white/70">
                Manage your account details and security
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-16 bg-brand-white min-h-[60vh]">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-3 space-y-8">
                <div class="border-2 border-brand-charcoal bg-brand-offwhite p-6 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <ul class="space-y-4">
                        <li>
                            <a href="{{ route('dashboard') }}" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-grey hover:text-brand-red transition-colors flex items-center justify-between group">
                                <span>&larr; Back to Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-9 space-y-16">
                
                <div class="border-2 border-brand-charcoal bg-brand-offwhite p-8 sm:p-12 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="border-2 border-brand-charcoal bg-brand-offwhite p-8 sm:p-12 shadow-[8px_8px_0px_0px_rgba(42,42,44,1)]">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="border-2 border-brand-red bg-brand-white p-8 sm:p-12">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
