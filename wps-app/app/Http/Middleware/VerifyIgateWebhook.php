<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate iGate (or partner) callbacks via Bearer token or X-Wps-Webhook-Secret.
 */
class VerifyIgateWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.igate.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            abort(404);
        }

        $provided = $request->bearerToken();
        if ($provided === null || $provided === '') {
            $provided = $request->header('X-Wps-Webhook-Secret');
        }

        if (! is_string($provided) || ! hash_equals($secret, $provided)) {
            abort(401, 'Invalid webhook authentication');
        }

        return $next($request);
    }
}
