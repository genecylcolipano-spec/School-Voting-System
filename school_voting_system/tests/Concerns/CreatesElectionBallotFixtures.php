<?php

namespace Tests\Concerns;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;

trait CreatesElectionBallotFixtures
{
    /**
     * @return array{
     *     election: Election,
     *     categories: list<ElectionCategory>,
     *     candidates: list<Candidate>
     * }
     */
    protected function createElectionBallot(int $positions = 2): array
    {
        $election = Election::factory()->active()->create();

        $categories = [];
        $candidates = [];

        for ($index = 0; $index < $positions; $index++) {
            $category = ElectionCategory::factory()->create([
                'election_id' => $election->id,
                'sort_order' => $index + 1,
            ]);

            $candidate = Candidate::factory()->create([
                'election_id' => $election->id,
                'election_category_id' => $category->id,
            ]);

            $categories[] = $category;
            $candidates[] = $candidate;
        }

        return compact('election', 'categories', 'candidates');
    }
}
