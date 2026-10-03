<?php

namespace App\Mcp\Tools;

use App\Models\ResearchProject;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Returns structured thesis project context for an authorized research assistant.')]
class ProjectContextTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:research_projects,id'],
        ]);

        $project = ResearchProject::query()
            ->with(['owner:id,name', 'members:id,name', 'tasks', 'feedback', 'papers:id,research_project_id,title,status'])
            ->findOrFail($validated['project_id']);

        return Response::json([
            'project' => [
                'id' => $project->id,
                'title' => $project->title,
                'discipline' => $project->discipline,
                'description' => $project->description,
                'status' => $project->status,
                'progress' => $project->progress,
            ],
            'owner' => $project->owner?->name,
            'members' => $project->members->map(fn ($member): array => [
                'name' => $member->name,
                'role' => $member->pivot->role,
            ])->values()->all(),
            'open_tasks' => $project->tasks->where('status', '!=', 'done')->map(fn ($task): string => $task->title)->values()->all(),
            'open_feedback' => $project->feedback->where('status', 'open')->map(fn ($feedback): string => $feedback->body)->values()->all(),
            'papers' => $project->papers->map(fn ($paper): array => [
                'title' => $paper->title,
                'status' => $paper->status,
            ])->values()->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()->description('The research project ID to inspect.')->required(),
        ];
    }
}
