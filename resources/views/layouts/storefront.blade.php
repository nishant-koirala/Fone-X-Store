<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white text-brand-charcoal antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FoneX Store — New, Certified Used & Trade-In Phones')</title>
    <meta name="description" content="FoneX Store: Premium destination for new phones, certified graded pre-owned devices, and instant trade-in valuations.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Mono:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-white font-sans text-brand-charcoal selection:bg-brand-red selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- Sticky Header -->
    <header class="sticky top-0 z-50 bg-white border-b-2 border-brand-red">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8 h-20">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="group flex items-center space-x-1 focus:outline-none">
                <span class="font-display text-3xl tracking-tight text-brand-charcoal">Fone</span>
                <span class="font-display text-3xl tracking-tight text-brand-red">X</span>
                <span class="ml-2 hidden sm:inline-block font-mono text-[10px] uppercase tracking-widest text-brand-grey border border-gray-200 px-1.5 py-0.5">STORE</span>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('products.index', ['grade' => 'new']) }}" class="font-sans text-sm font-semibold text-brand-charcoal hover:text-brand-red transition-colors duration-150 py-1 border-b-2 border-transparent hover:border-brand-red">
                    New Phones
                </a>
                <a href="{{ route('products.index', ['grade' => 'used']) }}" class="font-sans text-sm font-semibold text-brand-charcoal hover:text-brand-red transition-colors duration-150 py-1 border-b-2 border-transparent hover:border-brand-red">
                    Certified Used
                </a>
                <a href="{{ route('products.index') }}" class="font-sans text-sm font-semibold text-brand-charcoal hover:text-brand-red transition-colors duration-150 py-1 border-b-2 border-transparent hover:border-brand-red">
                    All Inventory
                </a>
                <a href="#trade-in" class="font-sans text-sm font-semibold text-brand-charcoal hover:text-brand-red transition-colors duration-150 py-1 border-b-2 border-transparent hover:border-brand-red">
                    Trade-In & Upgrade
                </a>
            </nav>

            <!-- Header Right Actions: Admin Link & Cart -->
            <div class="flex items-center space-x-4">
                @auth
                    <a href="/admin" class="hidden sm:inline-flex font-mono text-xs font-semibold uppercase text-brand-charcoal hover:text-brand-red border border-gray-300 hover:border-brand-red px-3 py-1.5 transition-colors">
                        Admin Portal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex font-mono text-xs font-semibold uppercase text-brand-charcoal hover:text-brand-red border border-gray-300 hover:border-brand-red px-3 py-1.5 transition-colors">
                        Sign In
                    </a>
                @endauth

                <!-- Cart Button -->
                <a href="{{ route('cart.index') }}" class="group relative inline-flex items-center justify-center p-2 text-brand-charcoal hover:text-brand-red transition-colors" aria-label="Shopping Cart">
                    <svg class="h-6 w-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" stroke-linejoin="miter" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center bg-brand-red font-mono text-[11px] font-bold text-white shadow-sm">
                        {{ array_sum(array_column(session('cart', []), 'quantity')) }}
                    </span>
                </a>

                <!-- Mobile Menu Button -->
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="inline-flex md:hidden items-center justify-center p-2 text-brand-charcoal hover:text-brand-red focus:outline-none"
                        aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Icon Hamburger -->
                    <svg x-show="!mobileMenuOpen" class="h-7 w-7 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Icon Close -->
                    <svg x-show="mobileMenuOpen" x-cloak class="h-7 w-7 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-gray-200 bg-white px-4 pt-3 pb-6">
            <div class="flex flex-col space-y-3 font-sans font-semibold text-base">
                <a href="#new-phones" @click="mobileMenuOpen = false" class="block py-2 text-brand-charcoal hover:text-brand-red border-b border-gray-100">
                    New Phones
                </a>
                <a href="#used-phones" @click="mobileMenuOpen = false" class="block py-2 text-brand-charcoal hover:text-brand-red border-b border-gray-100">
                    Certified Used
                </a>
                <a href="#trade-in" @click="mobileMenuOpen = false" class="block py-2 text-brand-charcoal hover:text-brand-red border-b border-gray-100">
                    Trade-In & Upgrade
                </a>
                <a href="#support" @click="mobileMenuOpen = false" class="block py-2 text-brand-charcoal hover:text-brand-red border-b border-gray-100">
                    Support
                </a>
                <div class="pt-2">
                    @auth
                        <a href="/admin" class="block w-full text-center font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red py-3 transition-colors">
                            Admin Portal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red py-3 transition-colors">
                            Sign In to Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="bg-emerald-500 text-white font-mono text-xs font-bold uppercase tracking-wider py-3 px-4 text-center">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-brand-red text-white font-mono text-xs font-bold uppercase tracking-wider py-3 px-4 text-center">
                ✕ {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-amber-500 text-white font-mono text-xs font-bold uppercase tracking-wider py-3 px-4 text-center">
                ⚠ {{ session('warning') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Column 1: Store Info -->
                <div class="space-y-3 md:col-span-2">
                    <div class="flex items-center space-x-1">
                        <span class="font-display text-2xl tracking-tight text-brand-charcoal">Fone</span>
                        <span class="font-display text-2xl tracking-tight text-brand-red">X</span>
                    </div>
                    <p class="max-w-md font-sans text-sm text-brand-grey leading-relaxed">
                        FoneX Store is the official destination for brand-new smartphones, certified pre-owned devices with transparent grading, and instant trade-in exchange valuations.
                    </p>
                    <p class="font-mono text-xs text-brand-charcoal font-medium">
                        OPERATING HOURS: MON-SAT 09:00 - 20:00 NPT
                    </p>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h3 class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal mb-3">Shop Directory</h3>
                    <ul class="space-y-2 font-sans text-sm text-brand-grey">
                        <li><a href="#new-phones" class="hover:text-brand-red transition-colors">New Smartphones</a></li>
                        <li><a href="#used-phones" class="hover:text-brand-red transition-colors">Certified Pre-Owned</a></li>
                        <li><a href="#trade-in" class="hover:text-brand-red transition-colors">Trade-In Valuation</a></li>
                        <li><a href="/admin" class="hover:text-brand-red transition-colors">Staff & Management Portal</a></li>
                    </ul>
                </div>

                <!-- Column 3: Trust & Policy -->
                <div>
                    <h3 class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal mb-3">Guarantees</h3>
                    <ul class="space-y-2 font-sans text-sm text-brand-grey">
                        <li><span class="text-brand-charcoal font-medium">12-Month Warranty</span> on all items</li>
                        <li><span class="text-brand-charcoal font-medium">70-Point Inspection</span> for used grades</li>
                        <li><span class="text-brand-charcoal font-medium">Secure Payments</span> & EMI option</li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="mt-12 border-t border-gray-100 pt-6 flex flex-col md:flex-row items-center justify-between font-mono text-xs text-brand-grey">
                <p>&copy; {{ date('Y') }} FONE-X STORE. ALL RIGHTS RESERVED.</p>
                <p class="mt-2 md:mt-0">DESIGNED WITH PRECISION • BUILT ON LARAVEL</p>
            </div>
        </div>
    </footer>

</body>
</html>
