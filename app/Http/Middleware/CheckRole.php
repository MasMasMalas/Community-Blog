<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Accepts one or more comma-separated roles as middleware parameters.
     * Example: check.role:admin
     *          check.role:moderator,admin
     *          check.role:author,moderator,admin
     *
     * Behaviour:
     *   - Unauthenticated users  → redirect to /login
     *   - Inactive accounts       → force logout + redirect to /login with message
     *   - Wrong role              → abort(403)
     *
     * @param  array<string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Must be authenticated first.
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 2. Inactive accounts are kicked out immediately.
        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
        }

        // 3. Role check — at least one of the given roles must match.
        if (! empty($roles) && ! in_array($user->role, $roles, strict: true)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
