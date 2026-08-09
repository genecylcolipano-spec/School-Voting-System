<?php

namespace Tests\Feature\Student;

use App\Models\Election;
use App\Models\User;
use App\Services\Election\StudentElectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class VotingAvailabilityTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_active_election_shows_vote_now_not_results(): void
    {
        ['election' => $election] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        $availability = app(StudentElectionService::class)->votingAvailability($election, $student);

        $this->assertSame('open', $availability['state']);
        $this->assertTrue($availability['can_vote']);
        $this->assertFalse($availability['can_view_results']);
    }

    public function test_published_results_are_hidden_during_active_voting(): void
    {
        ['election' => $election] = $this->createElectionBallot(1);
        $election->forceFill(['public_results_published' => true])->save();

        $student = User::factory()->create();
        $availability = app(StudentElectionService::class)->votingAvailability($election, $student);

        $this->assertSame('open', $availability['state']);
        $this->assertFalse($availability['can_view_results']);
    }

    public function test_published_results_are_visible_after_voting_ends(): void
    {
        $election = Election::factory()->closed()->create([
            'public_results_published' => true,
        ]);

        $student = User::factory()->create();
        $availability = app(StudentElectionService::class)->votingAvailability($election, $student);

        $this->assertSame('results_published', $availability['state']);
        $this->assertTrue($availability['can_view_results']);
        $this->assertFalse($availability['can_vote']);
    }
}
