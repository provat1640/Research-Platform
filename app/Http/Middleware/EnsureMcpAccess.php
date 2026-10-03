<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMcpAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.mcp.access_token');
        $providedToken = $request->bearerToken();

        if (blank($configuredToken) || ! hash_equals((string) $configuredToken, (string) $providedToken)) {
            return response()->json(['message' => 'MCP authorization is required.'], 401);
        }

        return $next($request);
    }
}
