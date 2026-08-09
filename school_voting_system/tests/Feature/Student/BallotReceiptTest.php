<?php

namespace Tests\Feature\Student;

use App\Models\BallotSubmission;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class BallotReceiptTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_ballot_receipt_is_created_on_complete_submission(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(2);
        $student = User::factory()->create();

        $this->actingAs($student)->post(route('student.voting.submit', $election), [
            'selections' => [
                (string) $categories[0]->id => $candidates[0]->id,
                (string) $categories[1]->id => $candidates[1]->id,
            ],
        ])->assertRedirect(route('student.voting.show', $election));

        $this->assertDatabaseCount('ballot_submissions', 1);

        $receipt = BallotSubmission::query()->where('user_id', $student->id)->first();
        $this->assertNotNull($receipt);
        $this->assertSame($election->id, $receipt->election_id);
        $this->assertMatchesRegularExpression('/^BR-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $receipt->receipt_token);
    }

    public function test_completed_ballot_cannot_reopen_success_page_without_flash(): void
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

        $this->actingAs($student)
            ->get(route('student.voting.show', $election))
            ->assertRedirect(route('student.voting.index'))
            ->assertSessionHas('error', 'You have already submitted your vote for this election.')
            ->assertDontSee('Vote Successfully Submitted');

        $this->assertDatabaseCount('ballot_submissions', 1);
    }
}
