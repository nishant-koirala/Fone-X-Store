<!DOCTYPE html>
<html lang="en" class="h-full bg-white text-brand-charcoal antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FoneX Store — Direct Imports & Certified Pre-Owned Phones')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Fone-X-Store is Nepal\'s premier destination for high-quality, certified pre-owned smartphones and premium direct import accessories.')">
    <meta name="keywords" content="@yield('meta_keywords', 'phones, smartphones, pre-owned, nepal, electronics, accessories, iphone, samsung')">
    <meta name="author" content="Fone-X-Store">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph (Facebook/LinkedIn) -->
    <meta property="og:title" content="@yield('title', 'FoneX Store — Direct Imports & Certified Pre-Owned Phones')">
    <meta property="og:description" content="@yield('meta_description', 'Nepal\'s premier destination for high-quality, certified pre-owned smartphones.')">
    <meta property="og:image" content="@yield('og_image', asset('images/default-og.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Fone-X-Store">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'FoneX Store')">
    <meta name="twitter:description" content="@yield('meta_description', 'Nepal\'s premier destination for high-quality, certified pre-owned smartphones.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/default-og.jpg'))">

    <!-- Google Fonts: Archivo Black, Sora, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Mono:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Compiled Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col font-sans bg-white text-brand-charcoal selection:bg-brand-red selection:text-white">

    <!-- Top Announcement Ticker Bar -->
    <div class="bg-brand-charcoal text-white font-mono text-[11px] font-semibold py-2 px-4 border-b border-white/10 overflow-hidden">
        <div class="mx-auto max-w-[90rem] flex items-center justify-between">
            <div class="flex items-center space-x-3 text-xs">
                <span class="inline-flex items-center space-x-1.5 bg-brand-red text-white px-2 py-0.5 font-bold uppercase tracking-wider text-[10px]">
                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                    <span>OFFICIAL STORE</span>
                </span>
                <span class="hidden sm:inline-block text-white/80">
                    ⚡ FREE NEPALWIDE DELIVERY • 100-POINT DIAGNOSTIC TEST • 7-DAY CHECK GUARANTEE
                </span>
            </div>
            <div class="flex items-center space-x-4 text-white/70">
                <span class="hidden md:inline-block">Kathmandu, Nepal</span>
                <a href="{{ route('trade-in.create') }}" class="text-white hover:text-brand-red transition-colors underline font-bold">
                    Trade-In Old Device &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Glassmorphism Sticky Navigation Header -->
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-white/70 backdrop-blur-xl border-b border-gray-100/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="group flex items-center space-x-1 focus:outline-none">
                        <span class="font-display text-3xl tracking-tight text-brand-charcoal">Fone</span>
                        <span class="font-display text-3xl tracking-tight text-brand-red group-hover:scale-105 transition-transform">X</span>
                        <span class="ml-2 font-mono text-[10px] uppercase tracking-widest text-brand-grey border border-gray-200 px-1.5 py-0.5 bg-brand-offwhite">STORE</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8 font-sans text-sm font-bold">
                    <a href="{{ route('products.index', ['grade' => 'new']) }}" class="group relative py-2 text-brand-charcoal hover:text-brand-red transition-colors">
                        <span>New Phones</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-red group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="{{ route('products.index', ['grade' => 'used']) }}" class="group relative py-2 text-brand-charcoal hover:text-brand-red transition-colors">
                        <span>Certified Used</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-red group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="{{ route('products.index', ['category' => 'accessories']) }}" class="group relative py-2 text-brand-charcoal hover:text-brand-red transition-colors">
                        <span>Accessories</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-red group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="{{ route('products.index') }}" class="group relative py-2 text-brand-charcoal hover:text-brand-red transition-colors">
                        <span>All Inventory</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-red group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="{{ route('trade-in.create') }}" class="group relative py-2 text-brand-charcoal hover:text-brand-red transition-colors flex items-center space-x-1">
                        <span class="inline-block h-2 w-2 rounded-full bg-brand-red animate-pulse"></span>
                        <span>Trade-In & Upgrade</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-red group-hover:w-full transition-all duration-300"></span>
                    </a>
                </nav>

                <!-- Header Actions: Admin Link & Cart Button -->
                <div class="flex items-center space-x-4">
                    
                    <!-- Search Input (Desktop) -->
                    <form method="GET" action="{{ route('products.index') }}" class="hidden lg:flex relative items-center">
                        <input type="text" name="search" placeholder="Search phones..." value="{{ request('search') }}" class="font-sans text-xs border border-gray-300 bg-white text-brand-charcoal pl-3 pr-8 py-2 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none shadow-sm placeholder-gray-400 w-48 transition-all focus:w-64">
                        <button type="submit" class="absolute right-2 text-brand-charcoal hover:text-brand-red">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>

                    @auth
                        <a href="/admin" class="hidden sm:inline-flex items-center space-x-1.5 font-mono text-xs font-bold uppercase text-brand-charcoal hover:text-brand-red border border-gray-300 hover:border-brand-red px-3.5 py-2 transition-all shadow-sm bg-white">
                            <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="square" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Admin Portal</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex font-mono text-xs font-bold uppercase text-brand-charcoal hover:text-brand-red border border-gray-300 hover:border-brand-red px-3.5 py-2 transition-all bg-white shadow-sm">
                            Sign In
                        </a>
                    @endauth

                    <!-- Wishlist Button -->
                    @php $wishlistCount = count(session('wishlist', [])); @endphp
                    <a href="{{ route('wishlist.index') }}" class="group relative hidden sm:inline-flex items-center justify-center p-2.5 text-brand-charcoal hover:text-brand-red transition-colors border border-gray-200 hover:border-brand-red bg-white shadow-sm" aria-label="Wishlist">
                        <svg class="h-6 w-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        
                        <!-- Badge -->
                        @if($wishlistCount > 0)
                            <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center bg-brand-charcoal font-mono text-[11px] font-bold text-white shadow-md">
                                {{ $wishlistCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Cart Button with Live Pulse Ring -->
                    @php $cartCount = array_sum(array_column(session('cart', []), 'quantity')); @endphp
                    <a href="{{ route('cart.index') }}" class="group relative inline-flex items-center justify-center p-2.5 text-brand-charcoal hover:text-brand-red transition-colors border border-gray-200 hover:border-brand-red bg-white shadow-sm" aria-label="Shopping Cart">
                        <svg class="h-6 w-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        
                        <!-- Badge -->
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center bg-brand-red font-mono text-[11px] font-bold text-white shadow-md">
                                {{ $cartCount }}
                            </span>
                            <span class="absolute -top-2 -right-2 h-5 w-5 bg-brand-red animate-ping opacity-75"></span>
                        @endif
                    </a>

                    <!-- Mobile Menu Trigger -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="inline-flex md:hidden items-center justify-center p-2 text-brand-charcoal hover:text-brand-red focus:outline-none"
                            aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg x-show="!mobileMenuOpen" class="h-7 w-7 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="h-7 w-7 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                </div>
            </div>
        </div>

        <!-- Mobile Collapsible Navigation Drawer -->
        <div x-show="mobileMenuOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-gray-200 bg-white/95 backdrop-blur-lg">
             
            <!-- Search Input (Mobile) -->
            <div class="px-4 pt-4 pb-2">
                <form method="GET" action="{{ route('products.index') }}" class="relative flex items-center">
                    <input type="text" name="search" placeholder="Search phones..." value="{{ request('search') }}" class="w-full font-sans text-base border border-gray-300 bg-white text-brand-charcoal pl-4 pr-10 py-3 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none shadow-sm placeholder-gray-400">
                    <button type="submit" class="absolute right-3 text-brand-charcoal hover:text-brand-red">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="space-y-1 px-4 pt-3 pb-6">
                <a href="{{ route('products.index', ['grade' => 'new']) }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-charcoal hover:bg-brand-offwhite hover:text-brand-red">
                    New Phones
                </a>
                <a href="{{ route('products.index', ['grade' => 'used']) }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-charcoal hover:bg-brand-offwhite hover:text-brand-red">
                    Certified Used
                </a>
                <a href="{{ route('products.index', ['category' => 'accessories']) }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-charcoal hover:bg-brand-offwhite hover:text-brand-red">
                    Shop Accessories
                </a>
                <a href="{{ route('products.index') }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-charcoal hover:bg-brand-offwhite hover:text-brand-red">
                    All Inventory
                </a>
                <a href="{{ route('blog.index') }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-charcoal hover:bg-brand-offwhite hover:text-brand-red">
                    Blog & News
                </a>
                <a href="{{ route('trade-in.create') }}" class="block px-3 py-2.5 font-sans text-base font-bold text-brand-red bg-brand-red/10 border border-brand-red/20 mt-2">
                    🔄 Trade-In & Upgrade Old Phone
                </a>

                <div class="pt-4 border-t border-gray-100">
                    @auth
                        <a href="/admin" class="block w-full text-center font-mono text-xs uppercase font-bold text-white bg-brand-charcoal hover:bg-brand-red py-3 transition-colors">
                            Admin Portal Dashboard
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

    <!-- Main Content Area with Flash Alert Notifications -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="bg-emerald-600 text-white font-mono text-xs font-bold uppercase tracking-wider py-3.5 px-4 text-center shadow-md animate-fade-in">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-brand-red text-white font-mono text-xs font-bold uppercase tracking-wider py-3.5 px-4 text-center shadow-md animate-fade-in">
                ✕ {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-amber-500 text-white font-mono text-xs font-bold uppercase tracking-wider py-3.5 px-4 text-center shadow-md animate-fade-in">
                ⚠ {{ session('warning') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 bg-brand-charcoal text-white pt-16 pb-12">
        <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-4">
                
                <!-- Column 1: Store Info & Mission -->
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center space-x-1">
                        <span class="font-display text-3xl tracking-tight text-white">Fone</span>
                        <span class="font-display text-3xl tracking-tight text-brand-red">X</span>
                        <span class="ml-2 font-mono text-[10px] uppercase tracking-widest text-white/70 border border-white/20 px-1.5 py-0.5">STORE NEPAL</span>
                    </div>
                    <p class="font-sans text-sm text-white/70 max-w-md leading-relaxed">
                        Kathmandu's premier store for factory-sealed smartphones, certified pre-owned devices, and instant device trade-ins. Every phone is backed by our 100-point diagnostic inspection and 7-day checking guarantee.
                    </p>
                    <div class="font-mono text-xs text-white/50 space-y-1">
                        <p>📍 Store Address: New Road, Kathmandu, Nepal</p>
                        <p>📞 Phone Contact: +977-9841234567 | Support Hours: 10 AM - 7 PM</p>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h3 class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red mb-4">Shop Directory</h3>
                    <ul class="space-y-2.5 font-sans text-sm text-white/70">
                        <li><a href="{{ route('products.index', ['grade' => 'new']) }}" class="hover:text-white transition-colors">Direct Brand New Phones</a></li>
                        <li><a href="{{ route('products.index', ['grade' => 'used']) }}" class="hover:text-white transition-colors">Certified Pre-Owned Inventory</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-white transition-colors">Complete Catalog</a></li>
                        <li><a href="{{ route('trade-in.create') }}" class="hover:text-white transition-colors text-brand-red font-bold">Instant Trade-In Valuation &rarr;</a></li>
                    </ul>
                </div>

                <!-- Column 3: Quality Guarantees -->
                <div>
                    <h3 class="font-mono text-xs font-bold uppercase tracking-widest text-brand-red mb-4">Quality Standards</h3>
                    <ul class="space-y-2.5 font-sans text-sm text-white/70">
                        <li class="flex items-center space-x-2">
                            <span class="text-brand-red font-bold">✓</span>
                            <span>7-Day Check Time Guarantee</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-brand-red font-bold">✓</span>
                            <span>100-Point Diagnostic Testing</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-brand-red font-bold">✓</span>
                            <span>Clean IMEI & Verified Device History</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-brand-red font-bold">✓</span>
                            <span>Cash on Delivery / Pay on Pickup</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="mt-12 border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between text-white/50 font-mono text-xs gap-4">
                <p>&copy; {{ date('Y') }} FoneX Store Nepal. All rights reserved.</p>
                <div class="flex items-center space-x-4">
                    <span>Payment Methods: COD • Mobile QR • Cash</span>
                    <a href="/admin" class="hover:text-white underline">Staff Portal</a>
                </div>
            </div>
        </div>
    </footer>
    <!-- Floating Speed Dial Widget -->
    <div x-data="{ chatOpen: false }" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        
        <!-- Speed Dial Items -->
        <div class="flex flex-col items-end space-y-3 mb-4 origin-bottom"
             x-show="chatOpen" 
             @click.away="chatOpen = false"
             x-transition:enter="transition-all cubic-bezier(0.4, 0, 0.2, 1) duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-50"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition-all ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-50"
             style="display: none;">
            
            <!-- Email -->
            <div class="flex items-center space-x-4 group cursor-pointer">
                <span class="bg-white text-brand-charcoal text-[13px] font-sans font-bold px-4 py-2 rounded-xl shadow-lg border border-gray-100 group-hover:text-brand-red transition-colors">Email Us</span>
                <a href="mailto:support@fonexstore.com" class="flex items-center justify-center h-12 w-12 rounded-full bg-white text-brand-charcoal shadow-xl hover:shadow-2xl transition-all duration-300 group-hover:scale-110 border border-gray-100 group-hover:ring-4 group-hover:ring-gray-200/50">
                    <svg class="w-5 h-5 group-hover:text-brand-red transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </a>
            </div>

            <!-- WhatsApp -->
            <div class="flex items-center space-x-4 group cursor-pointer">
                <span class="bg-white text-brand-charcoal text-[13px] font-sans font-bold px-4 py-2 rounded-xl shadow-lg border border-gray-100 group-hover:text-[#25D366] transition-colors">WhatsApp Us</span>
                <a href="https://wa.me/9779741661901" target="_blank" class="flex items-center justify-center h-12 w-12 rounded-full bg-[#25D366] text-white shadow-xl hover:shadow-2xl transition-all duration-300 group-hover:scale-110 group-hover:bg-[#20b858] group-hover:ring-4 group-hover:ring-[#25D366]/30">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 001.602 5.968L0 24l6.19-1.626A12 12 0 1011.944 0zm6.275 16.924c-.266.753-1.536 1.455-2.128 1.533-.55.074-1.258.14-3.612-.835-3.364-1.393-5.543-4.823-5.711-5.047-.168-.224-1.365-1.821-1.365-3.475 0-1.654.858-2.464 1.163-2.775.305-.31.666-.388.888-.388.222 0 .444.004.64.013.204.01.478-.077.747.57.283.678.97 2.37.1 2.37.1 1.056.241.134.346.368.591.233.245.474.521.724.887.678 1.576.993 1.93.993.354 0 1.25-.6 1.417-.806.168-.206.168-.382.118-.419-.05-.037-.184-.06-.388-.163-.205-.102-1.21-.598-1.399-.666-.188-.068-.326-.102-.464.102-.138.204-.531.666-.653.804-.122.138-.244.153-.448.051-.205-.102-.865-.319-1.648-1.018-.609-.543-1.02-1.214-1.142-1.419-.122-.204-.013-.315.089-.417.091-.091.205-.238.307-.357.102-.119.136-.204.204-.34.068-.136.034-.255-.017-.357-.051-.102-.464-1.121-.635-1.535-.166-.402-.335-.347-.464-.354-.122-.005-.262-.007-.403-.007-.14 0-.368.053-.56.262-.191.209-.73.713-.73 1.737s.748 2.015.852 2.155c.104.14 1.468 2.241 3.555 3.14.496.213.882.341 1.183.436.496.158.948.136 1.306.082.404-.06 1.21-.494 1.381-.971.17-.478.17-.889.119-.971-.051-.082-.187-.132-.392-.234z"/></svg>
                </a>
            </div>
        </div>

        <!-- Floating Button -->
        <button @click="chatOpen = !chatOpen" 
                class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-charcoal text-white shadow-[0_8px_30px_rgb(0,0,0,0.12)] hover:shadow-[0_8px_30px_rgba(220,38,38,0.3)] hover:bg-brand-red transition-all duration-300 hover:scale-110 hover:-translate-y-1 focus:outline-none z-50">
            <!-- Icon switches based on state -->
            <svg x-show="!chatOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="square" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <svg x-show="chatOpen" class="h-6 w-6" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Initialize AOS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
            });
        });
    </script>
</body>
</html>
