<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\ElectionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ElectionCategory>
 */
class ElectionCategoryFactory extends Factory
{
    protected $model = ElectionCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'election_id' => Election::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'sort_order' => fake()->numberBetween(1, 20),
            'max_selections' => 1,
        ];
    }
}
