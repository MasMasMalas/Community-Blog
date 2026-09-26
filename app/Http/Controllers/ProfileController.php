<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     *
     * Persyaratan: 3.1, 3.2, 3.3, 3.4
     */
    public function show(): View
    {
        $user = Auth::user();

        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     *
     * Persyaratan: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 18.1, 18.2, 18.3, 18.4, 18.5, 18.6, 18.7, 18.8
     */
    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar with UUID filename in avatars directory
            $path = $request->file('avatar')->storeAs(
                'avatars',
                Str::uuid().'.'.$request->file('avatar')->extension(),
                'public'
            );

            $data['avatar'] = $path;
        }

        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show the form for changing the user's password.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function changePassword(): View
    {
        return view('profile.change-password');
    }

    /**
     * Handle password change request.
     *
     * Persyaratan:
     *   3.7  – verifikasi kata sandi lama sebelum menyimpan yang baru
     *   3.8  – hash kata sandi baru + validasi kekuatan (sama seperti registrasi)
     */
    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        // Update the user's password with hashed value
        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Kata sandi Anda berhasil diubah.');
    }

    /**
     * Upload user avatar.
     *
     * Persyaratan: 3.5, 3.6, 18.1, 18.2, 18.3, 18.4, 18.5, 18.6, 18.7, 18.8
     */
    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,gif', 'max:2048'],
        ]);

        // Delete old avatar if exists
        if (Auth::user()->avatar) {
            Storage::disk('public')->delete(Auth::user()->avatar);
        }

        // Store new avatar with UUID filename
        $path = $request->file('avatar')->storeAs(
            'avatars',
            Str::uuid().'.'.$request->file('avatar')->extension(),
            'public'
        );

        Auth::user()->update(['avatar' => $path]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Foto profil Anda berhasil diperbarui.');
    }
}
