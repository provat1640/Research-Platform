<?php

namespace App\Http\Middleware;

use App\Models\ResearchProject;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $project = $request->route('project');

        if (! $user || ! $project instanceof ResearchProject) {
            return response()->json(['message' => 'A project membership is required.'], 403);
        }

        if ($project->owner_id !== $user->id && ! $project->members()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'You are not a member of this research project.'], 403);
        }

        return $next($request);
    }
}
