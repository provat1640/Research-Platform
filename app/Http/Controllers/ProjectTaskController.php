<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        return response()->json(['data' => $project->tasks()->with('assignee:id,name')->latest()->get()]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,normal,high'],
            'due_at' => ['nullable', 'date'],
        ]);

        $creator = $request->user() ?? $project->owner;
        $task = $project->tasks()->create([
            ...$validated,
            'created_by' => $creator->id,
        ])->load('assignee:id,name');

        return response()->json(['data' => $task], 201);
    }

    public function update(Request $request, ProjectTask $task): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:todo,in_progress,done']]);
        $task->update($validated);

        return response()->json(['data' => $task->fresh('assignee:id,name')]);
    }
}
