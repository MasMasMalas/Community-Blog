@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">Semua Tag</h1>
        <p class="text-gray-600">Jelajahi konten berdasarkan tag yang tersedia</p>
    </div>

    <!-- Tags Grid -->
    @if ($tags->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @foreach ($tags as $tag)
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    class="block bg-white rounded-lg shadow-md p-6 hover:shadow-lg hover:bg-blue-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold mb-2 text-gray-900">{{ $tag->name }}</h2>

                            @if ($tag->description)
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ $tag->description }}
                                </p>
                            @endif

                            <div class="text-sm text-gray-500">
                                <span class="inline-block mr-4">
                                    📄 {{ $tag->published_articles_count ?? 0 }} artikel
                                </span>
                            </div>
                        </div>

                        <div class="ml-2 text-2xl">→</div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        @if ($tags->hasPages())
            <div class="mt-8">
                {{ $tags->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-100 rounded-lg p-8 text-center">
            <p class="text-gray-600 mb-4">Belum ada tag tersedia.</p>
            <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Kembali ke Beranda
            </a>
        </div>
    @endif
</div>
@endsection
