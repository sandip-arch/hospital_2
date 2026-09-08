<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this area.');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account is currently ' . $user->status . '. Please contact administration.');
        }

        // Superadmin has access to everything
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if ($user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have permission to access this resource.');
    }
}
