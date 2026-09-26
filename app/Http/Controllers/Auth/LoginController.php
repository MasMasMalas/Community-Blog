<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before rate-limiting kicks in.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Decay window in seconds (15 minutes).
     */
    private const DECAY_SECONDS = 15 * 60;

    // -------------------------------------------------------------------------
    // Show form
    // -------------------------------------------------------------------------

    /**
     * Display the login form.
     *
     * Requirement 2.1
     */
    public function showForm(): View
    {
        return view('auth.login');
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    /**
     * Handle the login form submission.
     *
     * - Rate-limits to MAX_ATTEMPTS per DECAY_SECONDS window (Requirement 2.4).
     * - Authenticates using email + password via Auth::attempt() (Requirement 2.2).
     * - Checks is_active flag after a successful credential match (Requirement 14.6).
     * - Returns a generic error message on failure to avoid user-enumeration
     *   (Requirement 2.3).
     * - Regenerates session token after login to prevent session fixation
     *   (Requirement 2.6).
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey($request);

        // --- Rate-limit check ---------------------------------------------------
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.', [
                        'seconds' => $seconds,
                    ]),
                ]);
        }

        // --- Attempt authentication ---------------------------------------------
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Record the failed attempt.
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('Kredensial yang Anda masukkan tidak valid.'),
                ]);
        }

        // Credentials matched — check account status before allowing access.
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            // Log out immediately so no session is kept.
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'),
                ]);
        }

        // Successful login — clear rate-limit counter and regenerate session.
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Requirement 2.2 — redirect to intended URL or dashboard.
        return redirect()->intended(route('dashboard'));
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    /**
     * Log the current user out.
     *
     * Invalidates the session and regenerates the CSRF token.
     *
     * Requirement 2.5, 2.6
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build the rate-limiter key: sha256(email|ip).
     *
     * Combining email + IP prevents one user from being blocked by another
     * targeting their email from a different address, while still throttling
     * repeated guesses from the same source.
     */
    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')).'|'.$request->ip();
    }
}
