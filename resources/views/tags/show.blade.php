@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">Tag: {{ $tag->name }}</h1>
        @if ($tag->description)
            <p class="text-gray-600">{{ $tag->description }}</p>
        @endif
        <p class="text-sm text-gray-500 mt-2">
            Menampilkan {{ $articles->total() }} artikel dengan tag ini
        </p>
    </div>

    <!-- Articles Grid -->
    @if ($articles->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach ($articles as $article)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    @if ($article->thumbnail)
                        <img
                            src="{{ asset('storage/' . $article->thumbnail) }}"
                            alt="{{ $article->title }}"
                            class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">Belum ada gambar</span>
                        </div>
                    @endif

                    <div class="p-4">
                        <h2 class="text-lg font-bold mb-2 line-clamp-2">
                            <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-blue-600">
                                {{ $article->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            {{ $article->excerpt }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <span>{{ $article->user->name }}</span>
                            @if ($article->published_at)
                                <span>{{ $article->published_at->format('d M Y') }}</span>
                            @endif
                        </div>

                        @if ($article->categories->count() > 0)
                            <div class="mb-3 flex flex-wrap gap-1">
                                @foreach ($article->categories->take(2) as $category)
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <a
                            href="{{ route('articles.show', $article->slug) }}"
                            class="inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Baca Selengkapnya →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if ($articles->hasPages())
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-100 rounded-lg p-8 text-center">
            <p class="text-gray-600 mb-4">Belum ada artikel dengan tag ini.</p>
            <a href="{{ route('articles.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Kembali ke Daftar Artikel
            </a>
        </div>
    @endif
</div>
@endsection
