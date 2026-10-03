<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Authentication is required for research AI tools.'], 401);
        }

        if (! $user->onTrial() && ! $user->subscribed('premium_ai_tier')) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Start a trial or activate the premium research AI tier to continue.',
            ], 403);
        }

        return $next($request);
    }
}
