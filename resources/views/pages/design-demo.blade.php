<!DOCTYPE html>
<html lang="en" class="h-full bg-white antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Premium Design Demo - Typography & Whitespace</title>

    <!-- Experimental Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Override default typography to showcase the premium font */
        body { font-family: 'Outfit', sans-serif; }
        h1, h2, h3, h4, .display-font { font-family: 'Syne', sans-serif; }
    </style>
</head>
<body class="bg-[#fafafa] text-gray-900 selection:bg-black selection:text-white overflow-x-hidden">

    <!-- Minimalist Sticky Header -->
    <header class="fixed top-0 w-full z-50 bg-white/50 backdrop-blur-2xl border-b border-black/5 transition-all">
        <div class="mx-auto max-w-[100rem] px-8 sm:px-12 py-6 flex items-center justify-between">
            <div class="font-display font-bold text-2xl tracking-tighter uppercase">
                Fone<span class="text-red-600">X</span>
            </div>
            <nav class="hidden md:flex space-x-12 text-sm font-medium tracking-wide uppercase text-gray-500">
                <a href="#" class="text-black transition-colors">Catalog</a>
                <a href="#" class="hover:text-black transition-colors">Trade-In</a>
                <a href="#" class="hover:text-black transition-colors">Journal</a>
                <a href="#" class="hover:text-black transition-colors">Support</a>
            </nav>
            <div>
                <a href="{{ route('home') }}" class="text-xs uppercase font-bold tracking-widest border border-gray-200 px-6 py-3 rounded-full hover:border-black transition-all">Back to Live Site</a>
            </div>
        </div>
    </header>

    <!-- Massive Whitespace Hero Section -->
    <section class="min-h-screen flex items-center justify-center pt-32 pb-24 px-8 relative">
        <!-- Abstract gradient blob behind text -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-red-100 to-transparent blur-[120px] rounded-full opacity-60 pointer-events-none z-0"></div>
        
        <div class="max-w-[100rem] mx-auto w-full relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
            
            <div class="space-y-12 max-w-2xl" data-aos="fade-up" data-aos-duration="1200">
                <span class="text-sm font-semibold tracking-[0.2em] text-red-600 uppercase">Certified Pre-Owned</span>
                <h1 class="font-display text-[4rem] sm:text-[6rem] leading-[0.9] font-extrabold tracking-tighter uppercase text-black">
                    Tech,<br>Reborn.
                </h1>
                <p class="text-xl sm:text-2xl font-light text-gray-500 leading-relaxed">
                    Experience the pinnacle of refurbished electronics. Zero compromises, zero waste. Just pure performance.
                </p>
                <div class="pt-8">
                    <button class="bg-black text-white px-10 py-5 rounded-full uppercase tracking-widest text-sm font-semibold hover:bg-red-600 hover:scale-105 transition-all duration-300 shadow-[0_20px_40px_rgba(0,0,0,0.15)]">
                        Explore Collection
                    </button>
                </div>
            </div>

            <div class="relative w-full aspect-[4/5] bg-white rounded-[2rem] shadow-[0_40px_100px_rgba(0,0,0,0.06)] overflow-hidden flex items-center justify-center group" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="200">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-50 to-white z-0"></div>
                <div class="relative z-10 w-2/3 h-2/3 transform group-hover:scale-110 transition-transform duration-1000 ease-out flex items-center justify-center">
                    <svg class="w-full h-full text-gray-200" fill="currentColor" viewBox="0 0 24 24"><path d="M16 2H8C6.9 2 6 2.9 6 4v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm3-3H9V5h6v12z"/></svg>
                </div>
            </div>

        </div>
    </section>

    <!-- Product Grid Demo -->
    <section class="py-32 px-8 bg-white border-t border-gray-100">
        <div class="max-w-[100rem] mx-auto">
            <div class="flex items-end justify-between mb-20" data-aos="fade-up">
                <h2 class="font-display text-4xl sm:text-5xl font-bold uppercase tracking-tighter">Latest Arrivals</h2>
                <a href="#" class="text-sm font-semibold uppercase tracking-widest hover:text-red-600 transition-colors border-b border-black pb-1">View All</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 sm:gap-20">
                
                <!-- Demo Card 1 -->
                <div class="group cursor-pointer" data-aos="fade-up" data-aos-delay="0">
                    <div class="aspect-[3/4] bg-[#f5f5f7] rounded-3xl mb-8 overflow-hidden relative flex items-center justify-center">
                        <div class="absolute top-6 right-6 bg-white/80 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-bold tracking-wider uppercase shadow-sm z-10">Grade A</div>
                        <svg class="w-1/3 h-1/3 text-gray-300 group-hover:scale-110 group-hover:text-red-400 transition-all duration-700 ease-out" fill="currentColor" viewBox="0 0 24 24"><path d="M16 2H8C6.9 2 6 2.9 6 4v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm3-3H9V5h6v12z"/></svg>
                    </div>
                    <div class="space-y-3 px-2">
                        <div class="text-xs font-bold tracking-[0.15em] text-gray-400 uppercase">Apple</div>
                        <h3 class="font-display text-2xl font-bold uppercase tracking-tight group-hover:text-red-600 transition-colors">iPhone 13 Pro Max</h3>
                        <p class="text-lg font-medium text-gray-600">From Rs 120,000</p>
                    </div>
                </div>

                <!-- Demo Card 2 -->
                <div class="group cursor-pointer" data-aos="fade-up" data-aos-delay="150">
                    <div class="aspect-[3/4] bg-[#f5f5f7] rounded-3xl mb-8 overflow-hidden relative flex items-center justify-center">
                        <div class="absolute top-6 right-6 bg-white/80 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-bold tracking-wider uppercase shadow-sm z-10">Brand New</div>
                        <svg class="w-1/3 h-1/3 text-gray-300 group-hover:scale-110 group-hover:text-red-400 transition-all duration-700 ease-out" fill="currentColor" viewBox="0 0 24 24"><path d="M16 2H8C6.9 2 6 2.9 6 4v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm3-3H9V5h6v12z"/></svg>
                    </div>
                    <div class="space-y-3 px-2">
                        <div class="text-xs font-bold tracking-[0.15em] text-gray-400 uppercase">Samsung</div>
                        <h3 class="font-display text-2xl font-bold uppercase tracking-tight group-hover:text-red-600 transition-colors">Galaxy S23 Ultra</h3>
                        <p class="text-lg font-medium text-gray-600">From Rs 145,000</p>
                    </div>
                </div>

                <!-- Demo Card 3 -->
                <div class="group cursor-pointer" data-aos="fade-up" data-aos-delay="300">
                    <div class="aspect-[3/4] bg-[#f5f5f7] rounded-3xl mb-8 overflow-hidden relative flex items-center justify-center">
                        <div class="absolute top-6 right-6 bg-white/80 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-bold tracking-wider uppercase shadow-sm z-10">Grade B</div>
                        <svg class="w-1/3 h-1/3 text-gray-300 group-hover:scale-110 group-hover:text-red-400 transition-all duration-700 ease-out" fill="currentColor" viewBox="0 0 24 24"><path d="M16 2H8C6.9 2 6 2.9 6 4v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm3-3H9V5h6v12z"/></svg>
                    </div>
                    <div class="space-y-3 px-2">
                        <div class="text-xs font-bold tracking-[0.15em] text-gray-400 uppercase">Apple</div>
                        <h3 class="font-display text-2xl font-bold uppercase tracking-tight group-hover:text-red-600 transition-colors">iPhone 12</h3>
                        <p class="text-lg font-medium text-gray-600">From Rs 65,000</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Initialize AOS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 1000,
                once: true,
                offset: 100,
                easing: 'ease-out-cubic'
            });
        });
    </script>
</body>
</html>
