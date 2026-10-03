<?php

namespace Tests\Feature;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
