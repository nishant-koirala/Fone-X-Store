@extends('layouts.storefront')

@section('title', $post->title . ' - Blog | FoneX Store')
@section('meta_description', Str::limit(strip_tags($post->content), 150))
@section('og_image', $post->getFirstMediaUrl('thumbnail') ?: asset('images/default-og.jpg'))
@section('og_type', 'article')

@section('content')
<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="mb-8 text-center">
        <div class="font-mono text-xs uppercase text-brand-grey mb-4">
            {{ $post->published_at ? $post->published_at->format('F j, Y') : $post->created_at->format('F j, Y') }}
        </div>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-brand-charcoal mb-8">
            {{ $post->title }}
        </h1>
        
        @if($post->getFirstMediaUrl('thumbnail'))
            <div class="w-full aspect-[21/9] bg-brand-offwhite mb-12">
                <img src="{{ $post->getFirstMediaUrl('thumbnail') }}" class="w-full h-full object-cover" alt="{{ $post->title }}">
            </div>
        @endif
    </div>

    <!-- Prose Content -->
    <div class="prose prose-lg prose-slate max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:text-brand-charcoal prose-a:text-brand-red hover:prose-a:text-red-700">
        {!! $post->content !!}
    </div>

    <div class="mt-16 pt-8 border-t border-gray-200 text-center">
        <a href="{{ route('blog.index') }}" class="font-mono text-sm font-bold uppercase tracking-wider text-brand-charcoal hover:text-brand-red transition-colors inline-flex items-center space-x-2">
            <span>&larr;</span>
            <span>Back to Blog</span>
        </a>
    </div>

</div>
@endsection
