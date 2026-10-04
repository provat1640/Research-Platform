<?php

namespace Database\Factories;

use App\Models\Citation;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Citation>
 */
class CitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2018, 2026);
        $author = fake()->lastName();

        return [
            'research_project_id' => ResearchProject::factory(),
            'added_by_id' => User::factory(),
            'key' => strtolower($author).$year,
            'title' => fake()->sentence(6),
            'authors' => fake()->name().' et al.',
            'year' => (string) $year,
            'source' => 'Journal of Academic Computing, Vol. 12',
            'doi' => '10.1016/j.jac.'.fake()->numberBetween(1000, 9999),
            'url' => 'https://doi.org/10.1016/j.jac.'.fake()->numberBetween(1000, 9999),
            'in_text_tag' => '['.fake()->numberBetween(1, 20).']',
        ];
    }
}
