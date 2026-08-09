<?php

namespace Database\Factories;

use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    protected $model = Election::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->optional()->paragraph(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(2),
            'status' => ElectionStatus::Active,
            'created_by' => User::factory(),
            'is_paused' => false,
            'public_results_published' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => ElectionStatus::Active,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(2),
            'is_paused' => false,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subDay(),
            'is_paused' => false,
        ]);
    }
}
