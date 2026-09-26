<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ubah Kata Sandi — {{ config('app.name', 'Portal Berita') }}</title>

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

        {{-- Page title --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Ubah Kata Sandi</h1>
            <p class="text-gray-600">Perbarui kata sandi akun Anda untuk keamanan yang lebih baik</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">

            <form method="PUT" action="{{ route('profile.password') }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Current password --}}
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Kata Sandi Saat Ini
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900
                               placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-900
                               focus:ring-offset-2 transition
                               @error('current_password') border-red-500 @enderror"
                        placeholder="Masukkan kata sandi Anda saat ini"
                    >
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Kata Sandi Baru
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900
                               placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-900
                               focus:ring-offset-2 transition
                               @error('password') border-red-500 @enderror"
                        placeholder="Masukkan kata sandi baru Anda"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-600">
                        Kata sandi harus minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.
                    </p>
                </div>

                {{-- Password confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900
                               placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-900
                               focus:ring-offset-2 transition
                               @error('password_confirmation') border-red-500 @enderror"
                        placeholder="Konfirmasi kata sandi baru Anda"
                    >
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons --}}
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button
                        type="submit"
                        class="flex-1 rounded-md bg-gray-900 px-4 py-2.5 text-center text-sm font-semibold text-white
                               hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                               transition"
                    >
                        Simpan Kata Sandi Baru
                    </button>
                    <a
                        href="{{ route('profile.show') }}"
                        class="flex-1 rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-semibold text-gray-900
                               bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                               transition"
                    >
                        Batal
                    </a>
                </div>

            </form>

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
