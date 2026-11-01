<?php

namespace Database\Factories;

use App\Models\ProjectMedia;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMedia>
 */
class ProjectMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'research_project_id' => ResearchProject::factory(),
            'uploaded_by_id' => User::factory(),
            'title' => 'Figure '.fake()->numberBetween(1, 10).': '.fake()->sentence(4),
            'caption' => fake()->paragraph(),
            'file_path' => 'https://images.unsplash.com/photo-1507668077129-56e32842fceb?auto=format&fit=crop&w=800&q=80',
            'mime_type' => 'image/png',
            'size_bytes' => fake()->numberBetween(100000, 5000000),
            'figure_number' => fake()->numberBetween(1, 10),
        ];
    }
}
