<?php

namespace Database\Seeders;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $student = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Dr. Alex Kim',
            'email' => 'alex@example.com',
        ]);

        $projects = [
            ResearchProject::create([
                'owner_id' => $student->id,
                'title' => 'Adaptive Learning in Distributed Teams',
                'discipline' => 'Computer Science',
                'description' => 'Exploring how collaborative systems improve feedback loops in thesis research.',
                'progress' => 68,
                'last_activity_at' => now()->subMinutes(18),
            ]),
            ResearchProject::create([
                'owner_id' => $student->id,
                'title' => 'Climate Resilience in Urban Systems',
                'discipline' => 'Environmental Studies',
                'description' => 'Mapping practical resilience strategies for fast-growing cities.',
                'progress' => 42,
                'last_activity_at' => now()->subHours(3),
            ]),
        ];

        foreach ($projects as $project) {
            $project->members()->attach([
                $student->id => ['role' => 'student'],
                $teacher->id => ['role' => 'teacher'],
            ]);
        }
    }
}
