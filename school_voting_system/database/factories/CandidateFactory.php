<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'election_category_id' => ElectionCategory::factory(),
            'display_name' => fake()->name(),
            'party_or_group' => fake()->optional()->company(),
            'platform' => fake()->optional()->paragraph(),
            'is_active' => true,
            'eligibility_status' => 'verified',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Candidate $candidate): void {
            if ($candidate->election_category_id && ! $candidate->election_id) {
                $category = ElectionCategory::query()->find($candidate->election_category_id);
                $candidate->election_id = $category?->election_id;
            }
        });
    }
}
