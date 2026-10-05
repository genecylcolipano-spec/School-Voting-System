<?php

namespace Tests\Feature\Student;

use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\User;
use App\Models\Vote;
use App\Support\SchoolCourses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseScopedBallotTest extends TestCase
{
    use RefreshDatabase;

    public function test_bsit_student_sees_only_the_bsit_representative_race(): void
    {
        $fixture = $this->createCourseElection();
        $bsit = User::factory()->create(['course' => 'BSIT']);
        $crim = User::factory()->create(['course' => 'bs crim']);

        $this->actingAs($bsit)
            ->get(route('student.voting.show', $fixture['election']))
            ->assertOk()
            ->assertSee('President')
            ->assertSee('BSIT Representative')
            ->assertSee('Ana BSIT')
            ->assertDontSee('BSCRIM Representative')
            ->assertDontSee('Ben CRIM')
            ->assertDontSee('BEED Representative');

        $this->actingAs($crim)
            ->get(route('student.voting.show', $fixture['election']))
            ->assertOk()
            ->assertSee('President')
            ->assertSee('BSCRIM Representative')
            ->assertSee('Ben CRIM')
            ->assertDontSee('BSIT Representative')
            ->assertDontSee('Ana BSIT');
    }

    public function test_student_without_a_matching_course_only_sees_schoolwide_positions(): void
    {
        $fixture = $this->createCourseElection();
        $student = User::factory()->create(['course' => null]);

        $this->actingAs($student)
            ->get(route('student.voting.show', $fixture['election']))
            ->assertOk()
            ->assertSee('President')
            ->assertDontSee('BSIT Representative')
            ->assertDontSee('BSCRIM Representative');
    }

    public function test_bsit_student_completes_ballot_without_voting_other_courses(): void
    {
        $fixture = $this->createCourseElection();
        $student = User::factory()->create(['course' => 'BSIT']);

        $this->actingAs($student)
            ->post(route('student.voting.submit', $fixture['election']), [
                'selections' => [
                    (string) $fixture['president']->id => $fixture['presidentCandidate']->id,
                    (string) $fixture['bsit']->id => $fixture['bsitCandidate']->id,
                ],
            ])
            ->assertRedirect(route('student.voting.show', $fixture['election']))
            ->assertSessionHas('ballot_submitted', true);

        $this->assertDatabaseCount('votes', 2);
        $this->assertDatabaseHas('votes', [
            'user_id' => $student->id,
            'election_category_id' => $fixture['bsit']->id,
        ]);
        $this->assertDatabaseMissing('votes', [
            'user_id' => $student->id,
            'election_category_id' => $fixture['crim']->id,
        ]);
        $this->assertTrue($fixture['election']->fresh()->hasStudentCompletedBallot($student));
        $this->assertSame(1, $fixture['election']->fresh()->uniqueVoterCount());
    }

    public function test_bsit_student_cannot_vote_for_a_crim_representative(): void
    {
        $fixture = $this->createCourseElection();
        $student = User::factory()->create(['course' => 'BSIT']);

        $this->actingAs($student)
            ->from(route('student.voting.show', $fixture['election']))
            ->post(route('student.voting.submit', $fixture['election']), [
                'selections' => [
                    (string) $fixture['president']->id => $fixture['presidentCandidate']->id,
                    (string) $fixture['crim']->id => $fixture['crimCandidate']->id,
                ],
            ])
            ->assertRedirect(route('student.voting.show', $fixture['election']))
            ->assertSessionHasErrors('selections');

        $this->assertDatabaseCount('votes', 0);
        $this->assertFalse($fixture['election']->fresh()->hasStudentCompletedBallot($student));
    }

    public function test_cast_ballot_rejects_a_course_mismatch_even_if_called_directly(): void
    {
        $fixture = $this->createCourseElection();
        $student = User::factory()->create(['course' => 'BEED']);

        $this->expectException(\App\Exceptions\VoteIntegrityException::class);
        $this->expectExceptionMessage('This position is not available for your course.');

        Vote::castBallot($student, $fixture['bsitCandidate']);
    }

    /**
     * @return array{
     *     election: Election,
     *     president: ElectionCategory,
     *     bsit: ElectionCategory,
     *     crim: ElectionCategory,
     *     beed: ElectionCategory,
     *     presidentCandidate: Candidate,
     *     bsitCandidate: Candidate,
     *     crimCandidate: Candidate
     * }
     */
    protected function createCourseElection(): array
    {
        $election = Election::factory()->active()->create();

        $president = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'sort_order' => 1,
            'audience_course' => null,
        ]);
        $bsit = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'BSIT Representative',
            'sort_order' => 2,
            'audience_course' => SchoolCourses::BSIT,
        ]);
        $crim = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'BSCRIM Representative',
            'sort_order' => 3,
            'audience_course' => SchoolCourses::BSCRIM,
        ]);
        $beed = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'BEED Representative',
            'sort_order' => 4,
            'audience_course' => SchoolCourses::BEED,
        ]);

        $presidentCandidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $president->id,
            'display_name' => 'Pat President',
        ]);
        $bsitCandidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $bsit->id,
            'display_name' => 'Ana BSIT',
        ]);
        $crimCandidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $crim->id,
            'display_name' => 'Ben CRIM',
        ]);
        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $beed->id,
            'display_name' => 'Cara BEED',
        ]);

        return compact(
            'election',
            'president',
            'bsit',
            'crim',
            'beed',
            'presidentCandidate',
            'bsitCandidate',
            'crimCandidate',
        );
    }
}
