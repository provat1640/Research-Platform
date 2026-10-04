<?php

namespace Database\Factories;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchProject>
 */
class ResearchProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => fake()->sentence(4),
            'discipline' => fake()->randomElement(['Computer Science', 'Data Science', 'Information Systems', 'Economics']),
            'description' => fake()->paragraph(),
            'status' => 'draft',
            'progress' => fake()->numberBetween(10, 90),
            'research_question' => fake()->sentence().'?',
            'methodology' => fake()->randomElement(['Qualitative', 'Quantitative', 'Mixed methods', 'Design science']),
            'expected_outcome' => fake()->sentence(),
            'ethics_status' => 'approved',
            'paper_layout' => 'single_column',
            'paper_margin' => 'normal',
            'paper_size' => 'a4',
            'font_family' => 'serif',
            'line_spacing' => '1.5',
            'active_theme' => 'clean_white',
            'last_activity_at' => now(),
        ];
    }
}
