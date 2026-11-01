<?php

namespace Tests\Feature;

use App\Mcp\Tools\ProjectContextTool;
use App\Models\Citation;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
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

    public function test_can_export_citations_as_bibtex_json(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Export BibTeX Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        Citation::factory()->create([
            'research_project_id' => $project->id,
            'key' => 'turing1950',
            'title' => 'Computing Machinery and Intelligence',
            'authors' => 'Turing, A. M.',
            'year' => '1950',
            'source' => 'Mind, 59(236), 433-460',
            'doi' => '10.1093/mind/LIX.236.433',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/citations/export");

        $response->assertOk();
        $response->assertJsonPath('count', 1);
        $this->assertStringContainsString('@article{turing1950,', $response->json('bibtex'));
        $this->assertStringContainsString('author = {Turing, A. M.}', $response->json('bibtex'));
        $this->assertStringContainsString('doi = {10.1093/mind/LIX.236.433}', $response->json('bibtex'));
    }

    public function test_can_export_citations_as_downloadable_bib_file(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Download BibTeX Project',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        Citation::factory()->create([
            'research_project_id' => $project->id,
            'key' => 'shannon1948',
            'title' => 'A Mathematical Theory of Communication',
            'authors' => 'Shannon, C. E.',
            'year' => '1948',
        ]);

        $response = $this->actingAs($user)->get("/api/v1/projects/{$project->id}/citations/export?download=1");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/x-bibtex; charset=utf-8');
        $this->assertStringContainsString('@article{shannon1948,', $response->getContent());
    }

    public function test_project_context_mcp_tool_includes_citations_and_media(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'MCP Context Test',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        Citation::factory()->create([
            'research_project_id' => $project->id,
            'key' => 'knuth1984',
            'title' => 'Literate Programming',
            'authors' => 'Knuth, D. E.',
        ]);

        $project->media()->create([
            'title' => 'System Architecture Diagram',
            'file_path' => 'https://example.com/arch.png',
            'figure_number' => 1,
            'caption' => 'Overview diagram',
        ]);

        $tool = new ProjectContextTool;
        $request = new Request(['project_id' => $project->id]);
        $response = $tool->handle($request);

        $payload = json_decode((string) $response->content(), true);

        $this->assertSame('MCP Context Test', $payload['project']['title']);
        $this->assertCount(1, $payload['citations']);
        $this->assertSame('knuth1984', $payload['citations'][0]['key']);
        $this->assertCount(1, $payload['media']);
        $this->assertSame('System Architecture Diagram', $payload['media'][0]['title']);
    }
}
