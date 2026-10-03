<?php

namespace Tests\Feature;

use App\Models\ResearchProject;
use App\Models\User;
use App\Services\SupabaseClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResearchProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_can_be_created_and_listed(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Mira Sen']);

        $createResponse = $this->actingAs($user)->postJson('/api/v1/projects', [
            'title' => 'Adaptive Learning in Distributed Teams',
            'discipline' => 'Computer Science',
            'description' => 'A collaborative thesis workspace.',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Adaptive Learning in Distributed Teams')
            ->assertJsonPath('data.member_count', 1);

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonPath('data.0.owner', 'Dr. Mira Sen');

        $this->assertDatabaseHas('research_projects', [
            'title' => 'Adaptive Learning in Distributed Teams',
            'owner_id' => $user->id,
        ]);
    }

    public function test_project_workspace_returns_members_and_saves_document_versions(): void
    {
        $student = User::factory()->create(['name' => 'Mira Sen']);
        $teacher = User::factory()->create(['name' => 'Alex Kim']);
        $project = ResearchProject::create([
            'owner_id' => $student->id,
            'title' => 'A Collaborative Thesis',
        ]);
        $project->members()->attach([
            $student->id => ['role' => 'student'],
            $teacher->id => ['role' => 'teacher'],
        ]);

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.members')
            ->assertJsonPath('data.members.1.role', 'teacher');

        $this->actingAs($student)
            ->postJson("/api/v1/projects/{$project->id}/versions", [
                'content' => ['html' => '<p>First draft</p>'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.author', 'Mira Sen');

        $this->assertDatabaseHas('document_versions', [
            'research_project_id' => $project->id,
            'version_number' => 1,
        ]);
    }

    public function test_project_tasks_and_feedback_can_be_created_and_resolved(): void
    {
        $student = User::factory()->create(['name' => 'Mira Sen']);
        $project = ResearchProject::create([
            'owner_id' => $student->id,
            'title' => 'Reviewable Thesis',
        ]);
        $project->members()->attach($student->id, ['role' => 'student']);

        $taskResponse = $this->actingAs($student)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Clarify the research question',
            'priority' => 'high',
        ]);

        $taskResponse->assertCreated()->assertJsonPath('data.status', 'todo');
        $taskId = $taskResponse->json('data.id');

        $this->patchJson("/api/v1/tasks/{$taskId}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $feedbackResponse = $this->actingAs($student)->postJson("/api/v1/projects/{$project->id}/feedback", [
            'body' => 'Please support this claim with one more source.',
        ]);

        $feedbackResponse->assertCreated()->assertJsonPath('data.status', 'open');
        $feedbackId = $feedbackResponse->json('data.id');

        $this->patchJson("/api/v1/feedback/{$feedbackId}", ['status' => 'resolved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'resolved');
    }

    public function test_project_summary_uses_configured_openai_compatible_gateway(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'AI Assisted Thesis',
        ]);

        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'free-research-model');

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'model' => 'free-research-model',
                'choices' => [['message' => ['content' => 'Next action: refine the research question.']]],
            ]),
        ]);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertOk()
            ->assertJsonPath('data.model', 'free-research-model')
            ->assertJsonPath('data.content', 'Next action: refine the research question.');

        Http::assertSent(fn ($request): bool => $request->url() === 'https://ai.example.test/v1/chat/completions'
            && $request['model'] === 'free-research-model'
            && str_contains($request['messages'][1]['content'], 'AI Assisted Thesis'));
    }

    public function test_project_summary_fails_cleanly_when_ai_gateway_is_not_configured(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Offline Thesis']);
        config()->set('services.ai.base_url', null);
        config()->set('services.ai.model', null);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertServiceUnavailable();
    }

    public function test_supabase_client_reads_and_inserts_through_rest_api(): void
    {
        config()->set('services.supabase.url', 'https://example.supabase.co');
        config()->set('services.supabase.key', 'service-key');

        Http::fake([
            'https://example.supabase.co/rest/v1/research_projects*' => Http::sequence()
                ->push([['id' => 4, 'title' => 'Synced Thesis']])
                ->push([['id' => 5, 'title' => 'Created Thesis']]),
        ]);

        $client = app(SupabaseClient::class);

        $this->assertSame([['id' => 4, 'title' => 'Synced Thesis']], $client->select('research_projects'));
        $this->assertSame([['id' => 5, 'title' => 'Created Thesis']], $client->insert('research_projects', ['title' => 'Created Thesis']));

        Http::assertSentCount(2);
    }
}
