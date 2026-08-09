<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Partylist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partylist>
 */
class PartylistFactory extends Factory
{
    protected $model = Partylist::class;

    public function definition(): array
    {
        $name = rtrim(fake()->unique()->company(), '.');

        return [
            'election_id' => null,
            'name' => $name,
            'acronym' => strtoupper(substr(str_replace(' ', '', $name), 0, 3)),
            'color' => fake()->hexColor(),
            'motto' => fake()->catchPhrase(),
            'platform' => fake()->optional()->paragraph(),
            'description' => fake()->optional()->paragraph(),
            'leader' => fake()->optional()->name(),
            'status' => CampaignStatus::Active,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Active]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Draft]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Inactive]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Archived]);
    }
}
