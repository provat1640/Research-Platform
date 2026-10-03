<?php

namespace App\Http\Controllers;

use App\Models\ResearchProject;
use App\Services\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiController extends Controller
{
    public function summarize(Request $request, ResearchProject $project, AiGateway $ai): JsonResponse
    {
        $validated = $request->validate([
            'instruction' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->load(['members:id,name', 'tasks', 'feedback']);

        try {
            $result = $ai->chat([
                [
                    'role' => 'system',
                    'content' => 'You are a careful university research assistant. Do not invent sources, findings, or citations. Return concise, actionable academic guidance.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'instruction' => $validated['instruction'] ?? 'Summarize the current project state and recommend the next three research actions.',
                        'project' => [
                            'title' => $project->title,
                            'discipline' => $project->discipline,
                            'description' => $project->description,
                            'progress' => $project->progress,
                            'open_tasks' => $project->tasks->where('status', '!=', 'done')->pluck('title')->values(),
                            'open_feedback' => $project->feedback->where('status', 'open')->pluck('body')->values(),
                        ],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }

        return response()->json([
            'data' => [
                'model' => $result['model'] ?? config('services.ai.model'),
                'content' => data_get($result, 'choices.0.message.content'),
            ],
        ]);
    }
}
