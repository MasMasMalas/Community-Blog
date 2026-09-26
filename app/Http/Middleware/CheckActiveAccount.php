<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveAccount
{
    /**
     * Handle an incoming request.
     *
     * Applied globally to all authenticated routes.  When the currently
     * authenticated user's account has been deactivated, the session is
     * invalidated and the user is redirected to the login page with an
     * explanatory message — without requiring them to interact with any
     * other middleware or controller logic first.
     *
     * Requirement 14.6 — preventing deactivated accounts from using auth features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
        }

        return $next($request);
    }
}
