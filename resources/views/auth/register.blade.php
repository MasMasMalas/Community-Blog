<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar — {{ config('app.name', 'Portal Berita') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">

    {{-- Site header --}}
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900 hover:text-gray-700 transition">
            {{ config('app.name', 'Portal Berita') }}
        </a>
        <p class="mt-1 text-sm text-gray-500">Buat akun baru</p>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-md bg-white rounded-lg shadow-sm border border-gray-200 p-8">

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            {{-- Nama Lengkap --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Lengkap <span aria-hidden="true" class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    autofocus
                    required
                    value="{{ old('name') }}"
                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                           @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                    placeholder="Masukkan nama lengkap Anda"
                    aria-required="true"
                    aria-describedby="@error('name') name-error @else name-hint @enderror"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                >
                @error('name')
                    <p id="name-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                @else
                    <p id="name-hint" class="mt-1 text-xs text-gray-500">Nama yang akan ditampilkan di profil Anda.</p>
                @enderror
            </div>

            {{-- Username --}}
            <div class="mb-5">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                    Username <span aria-hidden="true" class="text-red-500">*</span>
                </label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    required
                    value="{{ old('username') }}"
                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                           @error('username') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                    placeholder="contoh: budi_123"
                    minlength="3"
                    maxlength="20"
                    pattern="[a-zA-Z0-9_]+"
                    aria-required="true"
                    aria-describedby="@error('username') username-error @else username-hint @enderror"
                    aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                >
                @error('username')
                    <p id="username-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                @else
                    <p id="username-hint" class="mt-1 text-xs text-gray-500">3–20 karakter. Hanya huruf, angka, dan garis bawah (_).</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Alamat Email <span aria-hidden="true" class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    value="{{ old('email') }}"
                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                           @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                    placeholder="anda@contoh.com"
                    aria-required="true"
                    aria-describedby="@error('email') email-error @enderror"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                >
                @error('email')
                    <p id="email-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Sandi <span aria-hidden="true" class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 pr-10
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        placeholder="••••••••"
                        minlength="8"
                        aria-required="true"
                        aria-describedby="password-hint @error('password') password-error @enderror"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    >
                    {{-- Toggle password visibility --}}
                    <button
                        type="button"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                        onclick="togglePassword('password', 'icon-eye', 'icon-eye-off')"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                    >
                        <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                   -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                                   a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                                   M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29
                                   M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                                   a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
                <p id="password-hint" class="mt-1 text-xs text-gray-500">Minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Konfirmasi Kata Sandi <span aria-hidden="true" class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 pr-10
                               focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent
                               border-gray-300"
                        placeholder="••••••••"
                        aria-required="true"
                        aria-label="Konfirmasi kata sandi"
                    >
                    {{-- Toggle password confirmation visibility --}}
                    <button
                        type="button"
                        aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi"
                        onclick="togglePassword('password_confirmation', 'icon-eye-confirm', 'icon-eye-off-confirm')"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                    >
                        <svg id="icon-eye-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                   -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="icon-eye-off-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                                   a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                                   M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29
                                   M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                                   a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white
                       hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2
                       transition"
            >
                Daftar
            </button>
        </form>

        {{-- Login link --}}
        <p class="mt-6 text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-gray-900 hover:underline underline-offset-4">
                Masuk sekarang
            </a>
        </p>
    </div>

    {{-- Footer --}}
    <p class="mt-6 text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name', 'Portal Berita') }}
    </p>

    <script>
        function togglePassword(inputId, eyeIconId, eyeOffIconId) {
            const input = document.getElementById(inputId);
            const iconEye = document.getElementById(eyeIconId);
            const iconEyeOff = document.getElementById(eyeOffIconId);

            if (input.type === 'password') {
                input.type = 'text';
                iconEye.classList.add('hidden');
                iconEyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconEye.classList.remove('hidden');
                iconEyeOff.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
