<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires the current Sanctum token to include the given ability (comma-separated).
 * Tokens issued before abilities were added must be re-created (log in again).
 */
class EnsureApiTokenAbilities
{
    public function handle(Request $request, Closure $next, string $abilities): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        foreach (array_filter(array_map('trim', explode(',', $abilities))) as $ability) {
            if (! $user->tokenCan($ability)) {
                abort(403, 'This API token is not allowed to perform this action.');
            }
        }

        return $next($request);
    }
}
