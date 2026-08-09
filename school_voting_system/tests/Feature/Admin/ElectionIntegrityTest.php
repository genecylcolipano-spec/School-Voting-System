<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vote;
use App\Services\Election\ElectionIntegrityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class ElectionIntegrityTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_integrity_hash_matches_after_refresh(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now(),
        ]);

        $election->refreshIntegrityHash();
        $service = app(ElectionIntegrityService::class);
        $result = $service->verify($election->fresh());

        $this->assertTrue($result['has_hash']);
        $this->assertTrue($result['valid']);
    }

    public function test_integrity_detects_tampered_vote_records(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now(),
        ]);

        $election->refreshIntegrityHash();

        Vote::query()->where('election_id', $election->id)->delete();

        $result = app(ElectionIntegrityService::class)->verify($election->fresh());

        $this->assertTrue($result['has_hash']);
        $this->assertFalse($result['valid']);
    }
}
