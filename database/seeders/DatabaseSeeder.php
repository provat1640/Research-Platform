<?php

namespace Database\Seeders;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $student = User::query()->updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'orcid_id' => '0000-0002-1825-0097',
            'password' => Hash::make('password'),
            'trial_ends_at' => now()->addDays(7),
            'is_teacher' => false,
        ]);

        $teacher = User::query()->updateOrCreate(['email' => 'alex@example.com'], [
            'name' => 'Dr. Alex Kim',
            'orcid_id' => '0000-0003-1415-926X',
            'password' => Hash::make('password'),
            'is_teacher' => true,
        ]);

        $coauthor = User::query()->updateOrCreate(['email' => 'mira@example.com'], [
            'name' => 'Mira Sen',
            'orcid_id' => '0000-0001-9876-5432',
            'password' => Hash::make('password'),
            'is_teacher' => false,
        ]);

        $projects = [
            ResearchProject::updateOrCreate(['owner_id' => $student->id, 'title' => 'Adaptive Learning in Distributed Teams'], [
                'discipline' => 'Computer Science',
                'description' => 'Exploring how collaborative systems improve feedback loops in thesis research.',
                'progress' => 68,
                'last_activity_at' => now()->subMinutes(18),
                'paper_layout' => 'single_column',
                'paper_margin' => 'normal',
                'line_spacing' => '1.5',
                'active_theme' => 'clean_white',
                'font_family' => 'serif',
            ]),
            ResearchProject::updateOrCreate(['owner_id' => $student->id, 'title' => 'Climate Resilience in Urban Systems'], [
                'discipline' => 'Environmental Studies',
                'description' => 'Mapping practical resilience strategies for fast-growing cities.',
                'progress' => 42,
                'last_activity_at' => now()->subHours(3),
            ]),
            ResearchProject::updateOrCreate(['owner_id' => $student->id, 'title' => 'Research Documentation Writing Lab'], [
                'discipline' => 'Research Communication',
                'description' => 'A journal-ready documentation project for methods, datasets, experiments, and reproducible findings.',
                'research_question' => 'How can research documentation make machine-learning experiments reproducible for reviewers?',
                'methodology' => 'Design science',
                'expected_outcome' => 'A journal-ready documentation template and tested evidence trail.',
                'ethics_status' => 'not_assessed',
                'progress' => 18,
                'last_activity_at' => now(),
            ]),
        ];

        foreach ($projects as $project) {
            $project->members()->syncWithoutDetaching([
                $student->id => ['role' => 'student'],
                $coauthor->id => ['role' => 'co-author'],
                $teacher->id => ['role' => 'supervisor'],
            ]);

            $project->citations()->updateOrCreate(['key' => 'hevner2004'], [
                'added_by_id' => $student->id,
                'title' => 'Design Science in Information Systems Research',
                'authors' => 'Hevner, A. R., March, S. T., Park, J., & Ram, S.',
                'year' => '2004',
                'source' => 'MIS Quarterly, 28(1), 75-105',
                'doi' => '10.2307/25148625',
                'url' => 'https://doi.org/10.2307/25148625',
                'in_text_tag' => '[1]',
            ]);

            $project->citations()->updateOrCreate(['key' => 'creswell2018'], [
                'added_by_id' => $teacher->id,
                'title' => 'Research Design: Qualitative, Quantitative, and Mixed Methods Approaches',
                'authors' => 'Creswell, J. W., & Creswell, J. D.',
                'year' => '2018',
                'source' => 'SAGE Publications (5th ed.)',
                'doi' => '10.1007/978-3-319-71058-7',
                'url' => 'https://us.sagepub.com/en-us/nam/research-design/book255675',
                'in_text_tag' => '[2]',
            ]);

            $project->media()->updateOrCreate(['title' => 'Figure 1: Collaborative Research Framework'], [
                'uploaded_by_id' => $coauthor->id,
                'caption' => 'Triangulation between qualitative feedback, quantitative milestones, and automated document synthesis in distributed graduate research teams.',
                'file_path' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1000&q=80',
                'figure_number' => 1,
                'mime_type' => 'image/jpeg',
            ]);
        }
    }
}
