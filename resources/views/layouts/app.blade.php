<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Community News'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold">{{ config('app.name') }}</a>
                    <div class="flex gap-4">
                        <a href="{{ route('articles.index') }}" class="hover:text-blue-600">Artikel</a>
                        <a href="{{ route('tags.index') }}" class="hover:text-blue-600">Tag</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-blue-600">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-12">
            <div class="container mx-auto px-4 py-6 text-center text-gray-600">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak cipta dilindungi.</p>
            </div>
        </footer>
    </div>
</body>
</html>
