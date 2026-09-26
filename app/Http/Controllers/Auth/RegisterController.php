<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * Persyaratan 1.1 — formulir registrasi dengan kolom:
     *   nama lengkap, username, alamat email, kata sandi, konfirmasi kata sandi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     *
     * Persyaratan:
     *   1.2  – email wajib unik (ditangani RegisterRequest)
     *   1.3  – username wajib unik (ditangani RegisterRequest)
     *   1.4  – format dan panjang username (ditangani RegisterRequest)
     *   1.5  – konfirmasi kata sandi (ditangani RegisterRequest)
     *   1.6  – password di-hash dengan bcrypt (melalui kolom 'password' yang di-cast 'hashed')
     *   1.7  – role default 'author'
     *   1.8  – pesan kesalahan (ditangani FormRequest + Blade)
     *   1.9  – redirect ke /login setelah sukses
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'author',
            'is_active' => true,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Akun Anda berhasil dibuat. Silakan masuk.');
    }
}
