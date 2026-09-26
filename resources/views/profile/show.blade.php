<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Profil — {{ config('app.name', 'Portal Berita') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-50">

    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900 hover:text-gray-700 transition">
                {{ config('app.name', 'Portal Berita') }}
            </a>
            <div class="flex gap-4">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 hover:text-gray-900">
                    Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Main content --}}
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Success message --}}
        @if (session('success'))
            <div role="alert" class="mb-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Page title --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Profil Saya</h1>
            <p class="text-gray-600">Kelola informasi profil dan pengaturan akun Anda</p>
        </div>

        {{-- Profile card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">

            {{-- Avatar --}}
            <div class="flex flex-col items-center mb-8 pb-8 border-b border-gray-200">
                @if ($user->avatar)
                    <img
                        src="{{ asset('storage/' . $user->avatar) }}"
                        alt="{{ $user->name }}"
                        class="h-24 w-24 rounded-full object-cover mb-4 border-2 border-gray-200"
                    >
                @else
                    <div class="h-24 w-24 rounded-full bg-gray-300 flex items-center justify-center mb-4 border-2 border-gray-200">
                        <span class="text-2xl font-bold text-gray-700">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                @endif
                <p class="text-sm text-gray-600 mb-4">Foto Profil</p>
            </div>

            {{-- Profile information --}}
            <div class="space-y-6 mb-8">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama
                    </label>
                    <p class="text-gray-900">{{ $user->name }}</p>
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Username
                    </label>
                    <p class="text-gray-900">{{ $user->username }}</p>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email
                    </label>
                    <p class="text-gray-900">{{ $user->email }}</p>
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bio
                    </label>
                    <p class="text-gray-900">
                        @if ($user->bio)
                            {{ $user->bio }}
                        @else
                            <span class="text-gray-400 italic">Belum ada bio</span>
                        @endif
                    </p>
                </div>

                {{-- Role --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Role
                    </label>
                    <span class="inline-block px-3 py-1 text-sm font-medium rounded-full
                        @if ($user->role === 'admin')
                            bg-red-100 text-red-800
                        @elseif ($user->role === 'moderator')
                            bg-blue-100 text-blue-800
                        @else
                            bg-green-100 text-green-800
                        @endif
                    ">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

            </div>

            {{-- Action buttons --}}
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex-1 rounded-md bg-gray-900 px-4 py-2.5 text-center text-sm font-semibold text-white
                           hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                           transition"
                >
                    Edit Profil
                </a>
                <a
                    href="{{ route('profile.password.form') }}"
                    class="flex-1 rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-semibold text-gray-900
                           bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                           transition"
                >
                    Ubah Kata Sandi
                </a>
            </div>

        </div>

    </div>

    {{-- Footer --}}
    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} {{ config('app.name', 'Portal Berita') }}
        </div>
    </footer>

</body>
</html>
