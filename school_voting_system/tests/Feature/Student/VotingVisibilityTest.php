<?php

namespace Tests\Feature\Student;

use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_do_not_see_draft_archived_or_annulled_elections(): void
    {
        $student = User::factory()->create();

        Election::factory()->active()->create(['title' => 'Visible Campus Election']);
        Election::factory()->closed()->create(['title' => 'Closed Campus Election']);
        Election::factory()->draft()->create(['title' => 'Hidden Draft Election']);
        Election::factory()->create([
            'title' => 'Hidden Archived Election',
            'status' => ElectionStatus::Archived,
        ]);
        Election::factory()->closed()->create([
            'title' => 'Hidden Annulled Election',
            'annulled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.voting.index'))
            ->assertOk()
            ->assertSee('Visible Campus Election')
            ->assertSee('Closed Campus Election')
            ->assertDontSee('Hidden Draft Election')
            ->assertDontSee('Hidden Archived Election')
            ->assertDontSee('Hidden Annulled Election');
    }

    public function test_draft_election_ballot_page_is_not_found(): void
    {
        $student = User::factory()->create();
        $draft = Election::factory()->draft()->create(['slug' => 'hidden-draft-election']);

        $this->actingAs($student)
            ->get(route('student.voting.show', $draft))
            ->assertNotFound();
    }

    public function test_faculty_do_not_see_draft_elections(): void
    {
        $faculty = User::factory()->faculty()->create();

        Election::factory()->active()->create(['title' => 'Faculty Visible Election']);
        Election::factory()->draft()->create(['title' => 'Faculty Hidden Draft']);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index'))
            ->assertOk()
            ->assertSee('Faculty Visible Election')
            ->assertDontSee('Faculty Hidden Draft');
    }
}
