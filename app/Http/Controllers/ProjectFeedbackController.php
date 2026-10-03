<?php

namespace App\Http\Controllers;

use App\Models\ProjectFeedback;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFeedbackController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        return response()->json(['data' => $project->feedback()->with('author:id,name')->latest()->get()]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'document_version_id' => ['nullable', 'integer', 'exists:document_versions,id'],
            'start_offset' => ['nullable', 'integer', 'min:0'],
            'end_offset' => ['nullable', 'integer', 'gte:start_offset'],
        ]);

        $author = $request->user() ?? $project->owner;
        $feedback = $project->feedback()->create([
            ...$validated,
            'author_id' => $author->id,
        ])->load('author:id,name');

        return response()->json(['data' => $feedback], 201);
    }

    public function update(Request $request, ProjectFeedback $feedback): JsonResponse
    {
        $feedback->load('project');
        $user = $request->user();

        abort_unless(
            $user && ($feedback->project->owner_id === $user->id
                || $feedback->project->members()->whereKey($user->id)->exists()),
            403,
            'You are not a member of this research project.'
        );

        $validated = $request->validate(['status' => ['required', 'in:open,resolved']]);
        $feedback->update($validated);

        return response()->json(['data' => $feedback->fresh('author:id,name')]);
    }
}
