<?php

namespace App\Http\Controllers;

use App\Events\DocumentVersionCreated;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $projects = ResearchProject::query()
            ->when($user, function ($query, $user) {
                $query->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id)
                        ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
                });
            })
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

        $activeEditor = null;
        if ($project->active_editor_id && $project->active_editor_at && $project->active_editor_at->diffInMinutes(now()) < 5) {
            $project->loadMissing('activeEditor:id,name');
            if ($project->activeEditor) {
                $activeEditor = [
                    'id' => $project->activeEditor->id,
                    'name' => $project->activeEditor->name,
                    'section' => $project->active_editor_section ?: 'Manuscript',
                    'active_at' => $project->active_editor_at->diffForHumans(),
                ];
            }
        }

        return response()->json(['data' => [
            ...$this->projectPayload($project),
            'description' => $project->description,
            'research_question' => $project->research_question,
            'methodology' => $project->methodology,
            'expected_outcome' => $project->expected_outcome,
            'ethics_status' => $project->ethics_status,
            'paper_layout' => $project->paper_layout ?? 'single_column',
            'paper_margin' => $project->paper_margin ?? 'normal',
            'paper_size' => $project->paper_size ?? 'a4',
            'font_family' => $project->font_family ?? 'serif',
            'line_spacing' => $project->line_spacing ?? '1.5',
            'active_theme' => $project->active_theme ?? 'clean_white',
            'active_editor' => $activeEditor,
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

    public function updateFormatting(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'paper_layout' => ['nullable', 'in:single_column,two_column'],
            'paper_margin' => ['nullable', 'in:normal,narrow,moderate,wide'],
            'paper_size' => ['nullable', 'in:a4,letter'],
            'font_family' => ['nullable', 'in:serif,sans,mono'],
            'line_spacing' => ['nullable', 'in:1.0,1.15,1.5,2.0'],
            'active_theme' => ['nullable', 'in:clean_white,warm_sepia,academic_dark'],
        ]);

        $project->update($validated);

        return response()->json(['data' => $project->fresh()]);
    }

    public function updatePresence(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'section' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        if ($user) {
            $project->update([
                'active_editor_id' => $user->id,
                'active_editor_section' => $validated['section'] ?? 'Manuscript',
                'active_editor_at' => now(),
            ]);
        }

        return response()->json([
            'data' => [
                'editor' => $user?->name,
                'section' => $validated['section'] ?? 'Manuscript',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function addMember(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:student,co-author,supervisor,reviewer'],
        ]);

        $targetUser = User::query()->where('email', $validated['email'])->first();

        if (! $targetUser) {
            $targetUser = User::create([
                'name' => explode('@', $validated['email'])[0],
                'email' => $validated['email'],
                'password' => bcrypt('password123'),
                'orcid_id' => '0000-0002-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'is_teacher' => in_array($validated['role'], ['supervisor', 'reviewer']),
            ]);
        }

        $project->members()->syncWithoutDetaching([
            $targetUser->id => ['role' => $validated['role']],
        ]);

        return response()->json([
            'message' => "Collaborator {$targetUser->name} added to project.",
            'data' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'role' => $validated['role'],
            ],
        ], 201);
    }

    public function removeMember(ResearchProject $project, User $user): JsonResponse
    {
        if ($project->owner_id === $user->id) {
            return response()->json(['message' => 'Project owner cannot be removed.'], 422);
        }

        $project->members()->detach($user->id);

        return response()->json(['message' => 'Collaborator removed from project.']);
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
