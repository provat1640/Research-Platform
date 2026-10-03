<?php

namespace App\Http\Controllers;

use App\Events\DocumentVersionCreated;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = ResearchProject::query()
            ->with('owner:id,name')
            ->withCount('members')
            ->latest('last_activity_at')
            ->latest()
            ->get()
            ->map(fn (ResearchProject $project): array => $this->projectPayload($project));

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'discipline' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $owner = $request->user() ?? User::query()->first();

        abort_unless($owner, 422, 'Create a user before creating a research project.');

        $project = ResearchProject::create([
            ...$validated,
            'owner_id' => $owner->id,
            'last_activity_at' => now(),
        ]);

        $project->members()->attach($owner->id, ['role' => 'student']);

        return response()->json([
            'data' => $this->projectPayload($project->load(['owner:id,name', 'members'])),
        ], 201);
    }

    public function show(ResearchProject $project): JsonResponse
    {
        $project->load([
            'owner:id,name',
            'members:id,name',
            'documentVersions' => fn ($query) => $query->with('author:id,name')->latest('version_number')->limit(1),
        ]);

        return response()->json(['data' => [
            ...$this->projectPayload($project),
            'description' => $project->description,
            'research_question' => $project->research_question,
            'methodology' => $project->methodology,
            'expected_outcome' => $project->expected_outcome,
            'ethics_status' => $project->ethics_status,
            'members' => $project->members->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->pivot->role,
            ])->values(),
            'latest_version' => $project->documentVersions->first()?->only(['id', 'content', 'version_number', 'created_at']),
        ]]);
    }

    public function updateSetup(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'research_question' => ['nullable', 'string', 'max:2000'],
            'methodology' => ['nullable', 'string', 'max:120'],
            'expected_outcome' => ['nullable', 'string', 'max:2000'],
            'ethics_status' => ['required', 'in:not_assessed,not_required,submitted,approved'],
        ]);

        $project->update($validated);

        return response()->json(['data' => $project->fresh()]);
    }

    public function storeVersion(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'array'],
        ]);

        $author = $request->user() ?? $project->owner;
        $versionNumber = ((int) $project->documentVersions()->max('version_number')) + 1;

        $version = $project->documentVersions()->create([
            'author_id' => $author->id,
            'content' => $validated['content'],
            'version_number' => $versionNumber,
        ])->load('author:id,name');

        $project->update(['last_activity_at' => now()]);
        DocumentVersionCreated::dispatch($version);

        return response()->json(['data' => [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'content' => $version->content,
            'author' => $version->author->name,
            'created_at' => $version->created_at,
        ]], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function projectPayload(ResearchProject $project): array
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'discipline' => $project->discipline,
            'description' => $project->description,
            'status' => $project->status,
            'progress' => $project->progress,
            'member_count' => $project->members_count ?? $project->members->count(),
            'owner' => $project->owner?->name,
            'last_activity' => $project->last_activity_at?->diffForHumans(),
        ];
    }
}
