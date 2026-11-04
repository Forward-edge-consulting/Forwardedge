<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        abort_unless($role === 'admin' && $request->user()?->role === 'admin', 403);

        return $next($request);
    }
}
