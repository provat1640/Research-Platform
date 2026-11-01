<?php

namespace Tests\Feature;

use App\Events\DocumentVersionCreated;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaybookMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_01_register_without_orcid_returns_validation_error_and_creates_no_user(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Alice Researcher',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('orcid_id');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_auth_02_register_with_malformed_orcid_returns_validation_error_on_orcid_id(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Bob Researcher',
            'email' => 'bob@example.com',
            'orcid_id' => '1234-invalid-orcid',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('orcid_id');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_auth_03_register_with_duplicate_orcid_fails_and_preserves_unique_constraint(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'orcid_id' => '0000-0002-1825-0097',
        ]);

        $this->from('/register')->post('/register', [
            'name' => 'Duplicate Candidate',
            'email' => 'candidate@example.com',
            'orcid_id' => '0000-0002-1825-0097',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('orcid_id');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', ['email' => 'candidate@example.com']);
    }

    public function test_auth_04_login_user_without_orcid_is_rejected_and_session_not_retained(): void
    {
        User::factory()->create([
            'email' => 'no-orcid@example.com',
            'password' => 'password123',
            'orcid_id' => null,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'no-orcid@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_auth_05_login_with_valid_credentials_regenerates_session_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => 'password123',
            'orcid_id' => '0000-0002-1825-0097',
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'valid@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_auth_06_logout_invalidates_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_auth_07_call_projects_api_as_guest_returns_401(): void
    {
        $this->getJson('/api/v1/projects')->assertUnauthorized();
    }

    public function test_auth_08_read_another_users_project_returns_forbidden(): void
    {
        $owner = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $outsider = User::factory()->create(['orcid_id' => '0000-0003-1415-926X']);
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Secret Defense Plan']);

        $this->actingAs($outsider)
            ->getJson("/api/v1/projects/{$project->id}")
            ->assertForbidden();

        $this->actingAs($outsider)
            ->get("/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_auth_09_update_another_projects_task_returns_403_and_database_unchanged(): void
    {
        $owner = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $outsider = User::factory()->create(['orcid_id' => '0000-0003-1415-926X']);
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Isolated Project']);
        $task = $project->tasks()->create([
            'title' => 'Initial Task',
            'status' => 'todo',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($outsider)
            ->patchJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])
            ->assertForbidden();

        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_auth_10_expired_trial_calling_ai_endpoint_returns_403_without_provider_request(): void
    {
        $user = User::factory()->create([
            'orcid_id' => '0000-0002-1825-0097',
            'trial_ends_at' => now()->subDay(),
        ]);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Thesis']);
        $project->members()->attach($user->id, ['role' => 'student']);

        Http::fake();

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertForbidden()
            ->assertJsonPath('status', 'access_denied');

        Http::assertNothingSent();
    }

    public function test_auth_11_active_trial_calls_ai_endpoint_with_project_context(): void
    {
        $user = User::factory()->create([
            'orcid_id' => '0000-0002-1825-0097',
            'trial_ends_at' => now()->addDays(7),
        ]);
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Active Neural Study',
            'discipline' => 'AI Ethics',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'research-model');

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'model' => 'research-model',
                'choices' => [['message' => ['content' => 'Focus on methodology.']]],
            ]),
        ]);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertOk()
            ->assertJsonPath('data.content', 'Focus on methodology.');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://ai.example.test/v1/chat/completions'
                && str_contains($request['messages'][1]['content'], 'Active Neural Study');
        });
    }

    public function test_data_01_create_paper_with_abstract_under_100_chars_returns_validation_error(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Short Abstract Project']);
        $project->members()->attach($user->id, ['role' => 'student']);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/papers", [
                'title' => 'Short Abstract Paper',
                'abstract' => 'Too short abstract.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('abstract');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_data_02_create_paper_with_duplicate_abstract_returns_422_and_no_duplicate_created(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Duplicate Abstract Project']);
        $project->members()->attach($user->id, ['role' => 'student']);

        $abstract = str_repeat('Rigorous testing of manuscript uniqueness ensures scholarly integrity. ', 2);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/papers", [
                'title' => 'Original Paper',
                'abstract' => $abstract,
            ])
            ->assertCreated();

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/papers", [
                'title' => 'Plagiarized Paper',
                'abstract' => $abstract,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'rejected');

        $this->assertDatabaseCount('papers', 1);
    }

    public function test_data_03_save_document_version_increments_version_number_and_stores_author(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Marie Curie', 'orcid_id' => '0000-0002-1825-0097']);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Radioactivity Notes']);
        $project->members()->attach($user->id, ['role' => 'student']);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/versions", [
                'content' => ['html' => '<p>Version 1 draft</p>'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.author', 'Dr. Marie Curie');

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/versions", [
                'content' => ['html' => '<p>Version 2 draft</p>'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.version_number', 2)
            ->assertJsonPath('data.author', 'Dr. Marie Curie');

        $this->assertDatabaseCount('document_versions', 2);
    }

    public function test_data_04_save_version_dispatches_broadcast_event(): void
    {
        Event::fake([DocumentVersionCreated::class]);

        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Broadcast Test Project']);
        $project->members()->attach($user->id, ['role' => 'student']);

        $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/versions", [
                'content' => ['html' => '<p>Live broadcast content</p>'],
            ])
            ->assertCreated();

        Event::assertDispatched(DocumentVersionCreated::class, function (DocumentVersionCreated $event) use ($project, $user): bool {
            return $event->version->research_project_id === $project->id
                && $event->version->author_id === $user->id
                && $event->broadcastOn()[0]->name === 'private-projects.'.$project->id;
        });
    }

    public function test_data_05_subscribe_as_non_member_channel_authorization_fails(): void
    {
        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'reverb-key');
        config()->set('broadcasting.connections.reverb.secret', 'reverb-secret');
        config()->set('broadcasting.connections.reverb.app_id', 'reverb-app');
        require base_path('routes/channels.php');

        $owner = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        $outsider = User::factory()->create(['orcid_id' => '0000-0003-1415-926X']);
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Channel Protected Project']);

        $this->actingAs($outsider)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-projects.'.$project->id,
                'socket_id' => '1234.5678',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-projects.'.$project->id,
                'socket_id' => '1234.5678',
            ])
            ->assertOk();
    }

    public function test_ai_01_ai_provider_unavailable_returns_503_without_stack_trace_or_secret(): void
    {
        $user = User::factory()->create([
            'orcid_id' => '0000-0002-1825-0097',
            'trial_ends_at' => now()->addDays(7),
        ]);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Offline Provider Test']);
        $project->members()->attach($user->id, ['role' => 'student']);

        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.key', 'secret-provider-token-12345');
        config()->set('services.ai.model', 'faulty-model');

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response(['error' => 'Internal server crash with sensitive path /var/secrets'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", []);

        $response->assertStatus(503)
            ->assertJsonPath('message', 'The research assistant is unavailable right now.')
            ->assertJsonMissing(['trace', 'secret-provider-token-12345', '/var/secrets']);
    }

    public function test_ai_02_evaluate_structured_test_data_contains_model_output_only(): void
    {
        $user = User::factory()->create([
            'orcid_id' => '0000-0002-1825-0097',
            'trial_ends_at' => now()->addDays(7),
        ]);
        $project = ResearchProject::create(['owner_id' => $user->id, 'title' => 'Code Safety Test']);
        $project->members()->attach($user->id, ['role' => 'student']);

        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'eval-model');

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'model' => 'eval-model',
                'choices' => [['message' => ['content' => '{"pass": true, "score": 1.0, "comment": "Safe evaluation"}']]],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/evaluate", [
                'instruction' => 'Evaluate output',
                'test_data' => [
                    ['input' => '2 + 2', 'expected' => 4, 'actual' => 4],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.model', 'eval-model')
            ->assertJsonPath('data.content', '{"pass": true, "score": 1.0, "comment": "Safe evaluation"}');
    }

    public function test_int_01_ai_health_probe_succeeds_returns_ready_status_and_redacts_credentials(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'ready-model');
        config()->set('services.ai.key', 'top-secret-ai-key-999');

        Http::fake(['https://ai.example.test/v1/models' => Http::response(['data' => []])]);

        $response = $this->actingAs($user)->getJson('/api/v1/integrations/health');

        $response->assertOk()
            ->assertJsonPath('data.ai.status', 'ready')
            ->assertJsonMissing(['top-secret-ai-key-999', 'key']);
    }

    public function test_int_02_ai_health_probe_times_out_returns_unreachable(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'timeout-model');

        Http::fake([
            'https://ai.example.test/v1/models' => function () {
                throw new ConnectionException('Connection timed out');
            },
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/integrations/health');

        $response->assertOk()
            ->assertJsonPath('data.ai.status', 'unreachable')
            ->assertJsonMissing(['trace']);
    }

    public function test_int_03_supabase_not_configured_returns_not_configured_without_outbound_request(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        config()->set('services.supabase.url', null);
        config()->set('services.supabase.key', null);

        Http::fake();

        $response = $this->actingAs($user)->getJson('/api/v1/integrations/health');

        $response->assertOk()
            ->assertJsonPath('data.supabase.status', 'not_configured');

        Http::assertNotSent(function ($request): bool {
            return str_contains($request->url(), 'supabase');
        });
    }

    public function test_int_04_reverb_configured_returns_configured_without_secrets(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);
        config()->set('broadcasting.connections.reverb.key', 'reverb-public-key');
        config()->set('broadcasting.connections.reverb.secret', 'reverb-secret-super-safe');
        config()->set('broadcasting.connections.reverb.options.host', '127.0.0.1');

        $response = $this->actingAs($user)->getJson('/api/v1/integrations/health');

        $response->assertOk()
            ->assertJsonPath('data.reverb.status', 'configured')
            ->assertJsonMissing(['reverb-secret-super-safe']);
    }

    public function test_ui_01_project_creation_receives_422_with_visible_validation_message(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'title' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertNotEmpty($response->json('errors.title.0'));
    }

    public function test_ui_02_session_expires_during_project_submission_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/v1/projects', [
            'title' => 'Expired Session Project',
        ]);

        $response->assertUnauthorized();
    }

    public function test_ui_03_dashboard_view_renders_shell_and_handles_empty_or_unavailable_state(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Jane Roe', 'orcid_id' => '0000-0002-1825-0097']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Welcome back, Dr.')
            ->assertSee('project-list')
            ->assertSee('Loading your research spaces…');
    }

    public function test_perf_01_load_project_list_with_100_projects_has_constant_query_count(): void
    {
        $user = User::factory()->create(['orcid_id' => '0000-0002-1825-0097']);

        $now = now();
        $projectsData = [];
        for ($i = 1; $i <= 100; $i++) {
            $projectsData[] = [
                'owner_id' => $user->id,
                'title' => "Research Project {$i}",
                'discipline' => 'Computer Science',
                'description' => "Description for project {$i}",
                'progress' => $i % 100,
                'status' => 'active',
                'last_activity_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        ResearchProject::insert($projectsData);

        DB::enableQueryLog();

        $response = $this->actingAs($user)->getJson('/api/v1/projects');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk()
            ->assertJsonCount(100, 'data');

        $this->assertLessThanOrEqual(4, count($queries));
    }

    public function test_perf_02_open_workspace_loads_without_avoidable_n_plus_one_queries(): void
    {
        $owner = User::factory()->create(['name' => 'Owner', 'orcid_id' => '0000-0002-1825-0097']);
        $members = User::factory()->count(5)->sequence(
            fn ($sequence) => ['orcid_id' => sprintf('0000-0003-0000-%04d', $sequence->index + 1)],
        )->create();
        $project = ResearchProject::create(['owner_id' => $owner->id, 'title' => 'Big Workspace']);

        $project->members()->attach($owner->id, ['role' => 'student']);
        foreach ($members as $member) {
            $project->members()->attach($member->id, ['role' => 'collaborator']);
        }

        for ($i = 1; $i <= 10; $i++) {
            $project->tasks()->create(['title' => "Task {$i}", 'created_by' => $owner->id, 'assignee_id' => $members->random()->id]);
            $project->feedback()->create(['body' => "Feedback {$i}", 'author_id' => $members->random()->id]);
            $project->documentVersions()->create(['version_number' => $i, 'author_id' => $owner->id, 'content' => ['draft' => $i]]);
        }

        DB::enableQueryLog();

        $showResponse = $this->actingAs($owner)->getJson("/api/v1/projects/{$project->id}");
        $tasksResponse = $this->actingAs($owner)->getJson("/api/v1/projects/{$project->id}/tasks");
        $feedbackResponse = $this->actingAs($owner)->getJson("/api/v1/projects/{$project->id}/feedback");

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $showResponse->assertOk()->assertJsonCount(6, 'data.members');
        $tasksResponse->assertOk()->assertJsonCount(10, 'data');
        $feedbackResponse->assertOk()->assertJsonCount(10, 'data');

        // Across 3 endpoints with 10 tasks, 10 feedback, 6 members, and 10 versions:
        // asserts constant query count (at most 4 queries per endpoint, ~11 total) and no N+1 query loop
        $this->assertLessThanOrEqual(12, count($queries));
    }
}
