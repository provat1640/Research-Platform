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

        $projects = [
            ResearchProject::updateOrCreate(['owner_id' => $student->id, 'title' => 'Adaptive Learning in Distributed Teams'], [
                'discipline' => 'Computer Science',
                'description' => 'Exploring how collaborative systems improve feedback loops in thesis research.',
                'progress' => 68,
                'last_activity_at' => now()->subMinutes(18),
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
                $teacher->id => ['role' => 'teacher'],
            ]);
        }
    }
}
