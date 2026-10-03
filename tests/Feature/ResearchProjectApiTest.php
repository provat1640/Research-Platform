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

    public function test_registration_requires_a_valid_orcid_id(): void
    {
        $this->post('/register', [
            'name' => 'New Researcher',
            'email' => 'researcher@example.com',
            'orcid_id' => 'not-an-orcid',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('orcid_id');

        $this->post('/register', [
            'name' => 'New Researcher',
            'email' => 'researcher@example.com',
            'orcid_id' => '0000-0002-1825-0097',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['orcid_id' => '0000-0002-1825-0097']);
    }

    public function test_login_rejects_an_account_without_orcid_identity(): void
    {
        User::factory()->create([
            'email' => 'missing-orcid@example.com',
            'password' => 'password123',
            'orcid_id' => null,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'missing-orcid@example.com',
            'password' => 'password123',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');
    }

    public function test_project_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/projects')->assertUnauthorized();
    }

    public function test_non_member_cannot_read_a_project(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Private Thesis']);

        $this->actingAs($outsider)
            ->getJson("/api/v1/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_non_member_cannot_update_project_task_or_feedback(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Protected Thesis']);
        $task = $project->tasks()->create(['title' => 'Private task', 'created_by' => $owner->id]);
        $feedback = $project->feedback()->create(['body' => 'Private feedback', 'author_id' => $owner->id]);

        $this->actingAs($outsider)->patchJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])->assertForbidden();
        $this->actingAs($outsider)->patchJson("/api/v1/feedback/{$feedback->id}", ['status' => 'resolved'])->assertForbidden();
    }

    public function test_project_research_setup_can_be_saved(): void
    {
        $student = User::factory()->create();
        $project = ResearchProject::create(['owner_id' => $student->id, 'title' => 'Methodology Study']);
        $project->members()->attach($student->id, ['role' => 'student']);

        $this->actingAs($student)
            ->patchJson("/api/v1/projects/{$project->id}/setup", [
                'research_question' => 'How do feedback loops change thesis quality?',
                'methodology' => 'Mixed methods',
                'expected_outcome' => 'A validated collaboration model.',
                'ethics_status' => 'submitted',
            ])
            ->assertOk()
            ->assertJsonPath('data.methodology', 'Mixed methods')
            ->assertJsonPath('data.ethics_status', 'submitted');
    }

    public function test_integration_health_never_returns_provider_credentials(): void
    {
        $user = User::factory()->create();
        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'research-model');
        config()->set('services.supabase.url', null);
        config()->set('services.supabase.key', null);
        Http::fake(['https://ai.example.test/v1/models' => Http::response(['data' => []])]);

        $this->actingAs($user)
            ->getJson('/api/v1/integrations/health')
            ->assertOk()
            ->assertJsonPath('data.ai.status', 'ready')
            ->assertJsonMissing(['key' => 'research-model']);
    }

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

        $this->actingAs($user)->getJson('/api/v1/projects')
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

        $this->actingAs($student)->getJson("/api/v1/projects/{$project->id}")
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

        $this->actingAs($student)->patchJson("/api/v1/tasks/{$taskId}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $feedbackResponse = $this->actingAs($student)->postJson("/api/v1/projects/{$project->id}/feedback", [
            'body' => 'Please support this claim with one more source.',
        ]);

        $feedbackResponse->assertCreated()->assertJsonPath('data.status', 'open');
        $feedbackId = $feedbackResponse->json('data.id');

        $this->actingAs($student)->patchJson("/api/v1/feedback/{$feedbackId}", ['status' => 'resolved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'resolved');
    }

    public function test_project_summary_uses_configured_openai_compatible_gateway(): void
    {
        $user = User::factory()->create(['trial_ends_at' => now()->addDay()]);
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
        $user = User::factory()->create(['trial_ends_at' => now()->addDay()]);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Offline Thesis']);
        config()->set('services.ai.base_url', null);
        config()->set('services.ai.model', null);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertServiceUnavailable();
    }

    public function test_llm_evaluation_accepts_structured_test_data(): void
    {
        $user = User::factory()->create(['trial_ends_at' => now()->addDay()]);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Testable Research']);
        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'free-research-model');
        Http::fake(['https://ai.example.test/v1/chat/completions' => Http::response([
            'model' => 'free-research-model',
            'choices' => [['message' => ['content' => '{"pass":true,"score":0.9}']]],
        ])]);

        $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/ai/evaluate", [
            'instruction' => 'Check whether the sample output matches the expected result.',
            'test_data' => [['input' => '2 + 2', 'expected' => 4, 'actual' => 4]],
        ])->assertOk()->assertJsonPath('data.model', 'free-research-model');
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

    public function test_papers_reject_duplicate_abstracts_and_create_unique_manuscripts(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Paper Project']);
        $abstract = str_repeat('This study evaluates collaborative thesis research methods. ', 3);

        $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/papers", [
            'title' => 'First Manuscript',
            'abstract' => $abstract,
        ])->assertCreated()->assertJsonPath('data.status', 'draft');

        $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/papers", [
            'title' => 'Duplicate Manuscript',
            'abstract' => $abstract,
        ])->assertStatus(422)->assertJsonPath('status', 'rejected');

        $this->assertDatabaseCount('papers', 1);
    }
}
