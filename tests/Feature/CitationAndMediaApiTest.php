<?php

namespace Tests\Feature;

use App\Models\Citation;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitationAndMediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_and_create_citations_for_project(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Citations Test Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/citations", [
            'title' => 'Attention Is All You Need',
            'authors' => 'Vaswani, A., et al.',
            'year' => '2017',
            'source' => 'NeurIPS',
            'doi' => '10.5555/3295222.3295349',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'Attention Is All You Need');
        $response->assertJsonPath('data.in_text_tag', '[1]');

        $list = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/citations");
        $list->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_delete_citation(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Delete Citation Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $citation = Citation::factory()->create(['research_project_id' => $project->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/projects/{$project->id}/citations/{$citation->id}");
        $response->assertOk();

        $this->assertDatabaseMissing('citations', ['id' => $citation->id]);
    }

    public function test_can_create_and_list_project_media(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Media Test Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/media", [
            'title' => 'Figure 1: Pipeline Overview',
            'caption' => 'The end-to-end architecture pipeline.',
            'image_url' => 'https://example.com/figure1.png',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.figure_number', 1);

        $list = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/media");
        $list->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_update_paper_formatting_and_margins(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Formatting Test Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)->patchJson("/api/v1/projects/{$project->id}/formatting", [
            'paper_layout' => 'two_column',
            'paper_margin' => 'narrow',
            'line_spacing' => '2.0',
            'font_family' => 'sans',
            'active_theme' => 'warm_sepia',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('research_projects', [
            'id' => $project->id,
            'paper_layout' => 'two_column',
            'paper_margin' => 'narrow',
            'line_spacing' => '2.0',
            'font_family' => 'sans',
            'active_theme' => 'warm_sepia',
        ]);
    }

    public function test_can_add_and_remove_collaborator_for_multiuser_workflow(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create(['email' => 'coauthor@example.com']);
        $project = ResearchProject::create([
            'owner_id' => $owner->id,
            'title' => 'Collaboration Test Project',
        ]);
        $project->members()->attach($owner->id, ['role' => 'student']);

        $addResponse = $this->actingAs($owner)->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => 'coauthor@example.com',
            'role' => 'co-author',
        ]);
        $addResponse->assertCreated();

        $this->assertDatabaseHas('project_members', [
            'research_project_id' => $project->id,
            'user_id' => $collaborator->id,
            'role' => 'co-author',
        ]);

        $removeResponse = $this->actingAs($owner)->deleteJson("/api/v1/projects/{$project->id}/members/{$collaborator->id}");
        $removeResponse->assertOk();

        $this->assertDatabaseMissing('project_members', [
            'research_project_id' => $project->id,
            'user_id' => $collaborator->id,
        ]);
    }

    public function test_can_record_active_editor_presence(): void
    {
        $user = User::factory()->create(['name' => 'Active Writer']);
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Presence Test Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/presence", [
            'section' => 'Methodology',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.editor', 'Active Writer');
        $response->assertJsonPath('data.section', 'Methodology');
    }
}
