@extends('layouts.storefront')

@section('title', 'Blog - News & Buying Guides | FoneX Store')

@section('content')
<div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="mb-12 text-center sm:text-left">
        <h1 class="font-display text-4xl uppercase tracking-tight text-brand-charcoal">The Fone-X Blog</h1>
        <p class="mt-2 font-mono text-sm text-brand-grey">Tech news, buying guides, and tips for buying used phones.</p>
    </div>

    @if($posts->isEmpty())
        <div class="bg-brand-offwhite p-12 text-center border border-gray-200">
            <h2 class="font-sans font-bold text-lg text-brand-charcoal">No posts yet</h2>
            <p class="text-brand-grey text-sm mt-1">Check back soon for exciting tech content.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group flex flex-col bg-white border border-gray-200 hover:border-brand-red hover:shadow-xl transition-all duration-300">
                    <div class="aspect-[16/9] bg-brand-offwhite overflow-hidden">
                        @if($post->getFirstMediaUrl('thumbnail'))
                            <img src="{{ $post->getFirstMediaUrl('thumbnail') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="square" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="font-mono text-[10px] uppercase text-brand-grey mb-3">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}
                        </div>
                        <h2 class="font-sans font-bold text-xl text-brand-charcoal group-hover:text-brand-red transition-colors mb-3">
                            {{ $post->title }}
                        </h2>
                        <p class="text-sm text-brand-grey line-clamp-3 mb-6 flex-1">
                            {{ Str::limit(strip_tags($post->content), 120) }}
                        </p>
                        <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand-red flex items-center space-x-1">
                            <span>Read Article</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
