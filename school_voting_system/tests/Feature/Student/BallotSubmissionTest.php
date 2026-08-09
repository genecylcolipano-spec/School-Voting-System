<?php

namespace Tests\Feature\Student;

use App\Enums\ElectionStatus;
use App\Models\BallotSubmission;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class BallotSubmissionTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_student_can_submit_complete_ballot_during_active_election(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(2);
        $student = User::factory()->create();

        $selections = [
            (string) $categories[0]->id => $candidates[0]->id,
            (string) $categories[1]->id => $candidates[1]->id,
        ];

        $response = $this->actingAs($student)->post(route('student.voting.submit', $election), [
            'selections' => $selections,
        ]);

        $response->assertRedirect(route('student.voting.show', $election));
        $response->assertSessionHas('ballot_submitted', true);
        $response->assertSessionHas('ballot_submitted_'.$election->id, true);

        $this->assertDatabaseCount('votes', 2);
        $this->assertDatabaseCount('ballot_submissions', 1);
        $this->assertTrue($election->fresh()->hasStudentCompletedBallot($student));
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_ballot_submitted',
        ]);
    }

    public function test_success_page_is_only_shown_once_after_submission(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        $this->actingAs($student)->post(route('student.voting.submit', $election), [
            'selections' => [
                (string) $categories[0]->id => $candidates[0]->id,
            ],
        ])->assertRedirect(route('student.voting.show', $election));

        $this->actingAs($student)
            ->get(route('student.voting.show', $election))
            ->assertOk()
            ->assertSee('Vote Successfully Submitted', false);

        $this->actingAs($student)
            ->get(route('student.voting.show', $election))
            ->assertRedirect(route('student.voting.index'))
            ->assertSessionHas('error', 'You have already submitted your vote for this election.');
    }

    public function test_student_cannot_submit_partial_ballot(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(2);
        $student = User::factory()->create();

        $response = $this->actingAs($student)->from(route('student.voting.show', $election))
            ->post(route('student.voting.submit', $election), [
                'selections' => [
                    (string) $categories[0]->id => $candidates[0]->id,
                ],
            ]);

        $response->assertRedirect(route('student.voting.show', $election));
        $response->assertSessionHasErrors('selections');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_student_cannot_vote_twice_in_same_category(): void
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

        BallotSubmission::recordFor($student, $election);

        $otherCandidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'display_name' => 'Alternate Candidate',
        ]);

        $response = $this->actingAs($student)->from(route('student.voting.show', $election))
            ->post(route('student.voting.submit', $election), [
                'selections' => [
                    (string) $categories[0]->id => $otherCandidate->id,
                ],
            ]);

        $response->assertRedirect(route('student.voting.index'));
        $response->assertSessionHas('error', 'You have already submitted your vote for this election.');
        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseHas('votes', [
            'user_id' => $student->id,
            'candidate_id' => $candidates[0]->id,
        ]);
    }

    public function test_legacy_single_vote_route_is_rejected(): void
    {
        ['election' => $election, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        $response = $this->actingAs($student)->post(route('student.voting.cast', $candidates[0]));

        $response->assertRedirect(route('student.voting.show', $election));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_student_cannot_submit_ballot_when_election_is_closed(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $election->forceFill([
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subDay(),
        ])->save();

        $student = User::factory()->create();

        $response = $this->actingAs($student)->post(route('student.voting.submit', $election), [
            'selections' => [
                (string) $categories[0]->id => $candidates[0]->id,
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_student_can_submit_remaining_positions_after_prior_votes(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(2);
        $student = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('student.voting.submit', $election), [
            'selections' => [
                (string) $categories[1]->id => $candidates[1]->id,
            ],
        ]);

        $response->assertRedirect(route('student.voting.show', $election));
        $response->assertSessionHas('ballot_submitted', true);
        $this->assertDatabaseCount('votes', 2);
        $this->assertTrue($election->fresh()->hasStudentCompletedBallot($student));
    }
}
