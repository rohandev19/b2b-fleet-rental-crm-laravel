<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        $allowedRoles = array_intersect($roles, UserRole::values());

        if ($allowedRoles === [] || ! $request->user()->hasRole(...$allowedRoles)) {
            abort(403);
        }

        return $next($request);
    }
}
