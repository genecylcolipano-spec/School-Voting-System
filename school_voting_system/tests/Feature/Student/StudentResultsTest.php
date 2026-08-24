<?php

namespace Tests\Feature\Student;

use App\Enums\ElectionStatus;
use App\Models\BallotSubmission;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use App\Services\Student\StudentResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentResultsTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_results_index_uses_student_portal(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('View official results of student elections and talent competitions')
            ->assertDontSee('Back to dashboard');
    }

    public function test_open_election_hides_vote_now_after_student_submits(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();
        $other = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now(),
        ]);
        BallotSubmission::recordFor($student, $election);

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($election->title)
            ->assertSee('You have voted')
            ->assertDontSee('Vote Now');

        $this->actingAs($other)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($election->title)
            ->assertSee('Vote Now')
            ->assertDontSee('You have voted');
    }

    public function test_closed_unpublished_election_detail_is_not_marked_open(): void
    {
        $election = Election::factory()->closed()->create();
        $detail = app(StudentResultsService::class)->electionDetail($election);

        $this->assertFalse($detail['is_official']);
        $this->assertFalse($detail['is_open']);
        $this->assertSame('Under Review', $detail['student_status']);
        $this->assertSame('review', $detail['student_status_tone']);
        $this->assertSame([], $detail['rankings']);
        $this->assertNull($detail['statistics']);
    }

    public function test_official_election_rankings_render_vote_percentage_bars(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now()->subHour(),
        ]);

        $election->forceFill([
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subDay(),
            'public_results_published' => true,
            'results_published_at' => now()->subHour(),
        ])->save();

        $this->actingAs($student)
            ->get(route('student.results.election.show', $election))
            ->assertOk()
            ->assertSee('Full Rankings')
            ->assertSee($candidates[0]->display_name)
            ->assertSee('100.0%')
            ->assertSee('role="progressbar"', false)
            ->assertSee('width: 100.0%', false);
    }
}
