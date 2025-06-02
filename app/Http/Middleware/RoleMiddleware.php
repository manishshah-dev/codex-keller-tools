<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        $allowed = explode(',', $roles);
        foreach ($allowed as $role) {
            if ($user && $user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        abort(403);
    }
}
