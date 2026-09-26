<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Profil — {{ config('app.name', 'Portal Berita') }}</title>

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
            <a href="{{ route('profile.show') }}" class="text-sm text-gray-600 hover:text-gray-900 mb-4 inline-block">
                ← Kembali ke Profil
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Profil</h1>
            <p class="text-gray-600">Perbarui informasi profil Anda</p>
        </div>

        {{-- Profile form --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name', $user->name) }}"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        placeholder="Nama lengkap Anda"
                        aria-describedby="@error('name') name-error @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    >
                    @error('name')
                        <p id="name-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="mb-6">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        required
                        value="{{ old('username', $user->username) }}"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('username') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        placeholder="username_Anda"
                        aria-describedby="@error('username') username-error @enderror"
                        aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                    >
                    <p class="mt-1 text-xs text-gray-500">Hanya huruf, angka, dan underscore</p>
                    @error('username')
                        <p id="username-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        value="{{ old('email', $user->email) }}"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        placeholder="anda@contoh.com"
                        aria-describedby="@error('email') email-error @enderror"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    @error('email')
                        <p id="email-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bio --}}
                <div class="mb-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                        Bio <span class="text-gray-400 text-xs">(maksimal 300 karakter)</span>
                    </label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="4"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('bio') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        placeholder="Ceritakan sedikit tentang Anda..."
                        aria-describedby="@error('bio') bio-error @enderror bio-counter"
                        aria-invalid="{{ $errors->has('bio') ? 'true' : 'false' }}"
                    >{{ old('bio', $user->bio) }}</textarea>
                    <div class="mt-1 flex justify-between items-center">
                        <p class="text-xs text-gray-500">{{ strlen(old('bio', $user->bio ?? '')) }}/300 karakter</p>
                        @error('bio')
                            <p id="bio-error" class="text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Avatar --}}
                <div class="mb-6">
                    <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">
                        Foto Profil <span class="text-gray-400 text-xs">(JPEG, PNG, GIF - max 2MB)</span>
                    </label>
                    <input
                        id="avatar"
                        name="avatar"
                        type="file"
                        accept="image/jpeg,image/png,image/gif"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('avatar') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        aria-describedby="@error('avatar') avatar-error @enderror"
                        aria-invalid="{{ $errors->has('avatar') ? 'true' : 'false' }}"
                    >
                    @error('avatar')
                        <p id="avatar-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons --}}
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <a
                        href="{{ route('profile.show') }}"
                        class="flex-1 rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-semibold text-gray-700
                               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                               transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="flex-1 rounded-md bg-gray-900 px-4 py-2.5 text-center text-sm font-semibold text-white
                               hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                               transition"
                    >
                        Simpan Perubahan
                    </button>
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
