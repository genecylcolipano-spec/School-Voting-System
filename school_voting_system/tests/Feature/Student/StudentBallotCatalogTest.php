<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Services\Student\StudentBallotCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentBallotCatalogTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_ballot_catalog_is_cached_and_cleared_when_a_candidate_changes(): void
    {
        $student = User::factory()->create();
        $fixture = $this->createElectionBallot(1);
        $election = $fixture['election'];
        $candidate = $fixture['candidates'][0];
        $originalName = $candidate->display_name;

        $this->actingAs($student)
            ->get(route('student.voting.show', $election))
            ->assertOk()
            ->assertSee($originalName);

        $this->assertTrue(Cache::has(StudentBallotCatalog::cacheKey($election->id)));

        $candidate->update(['display_name' => 'Updated Ballot Name']);

        $this->assertFalse(Cache::has(StudentBallotCatalog::cacheKey($election->id)));

        $this->actingAs($student)
            ->get(route('student.voting.show', $election))
            ->assertOk()
            ->assertSee('Updated Ballot Name');
    }
}
