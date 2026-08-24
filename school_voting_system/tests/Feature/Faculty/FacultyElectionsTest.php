<?php

namespace Tests\Feature\Faculty;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyElectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_elections_index_has_all_and_open_filters(): void
    {
        $faculty = User::factory()->faculty()->create();
        $open = Election::factory()->active()->create(['title' => 'Open Campus Vote']);
        $closed = Election::factory()->closed()->create(['title' => 'Closed Campus Vote']);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index'))
            ->assertOk()
            ->assertSee('All')
            ->assertSee('Open')
            ->assertSee($open->title)
            ->assertSee($closed->title)
            ->assertDontSee('Vote Now');

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index', ['filter' => 'open']))
            ->assertOk()
            ->assertSee($open->title)
            ->assertDontSee($closed->title);
    }

    public function test_paused_election_is_labeled_paused_not_active(): void
    {
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Paused Campus Vote',
            'is_paused' => true,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index'))
            ->assertOk()
            ->assertSee('Paused Campus Vote')
            ->assertSee('Paused')
            ->assertDontSee('>Active</span>', false);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.show', $election))
            ->assertOk()
            ->assertSee('Paused')
            ->assertDontSee('Vote Now')
            ->assertDontSee('View official results');
    }

    public function test_published_closed_election_links_to_official_results(): void
    {
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create([
            'title' => 'Published Campus Vote',
            'public_results_published' => true,
            'results_published_at' => now()->subHour(),
        ]);

        $resultsUrl = route('faculty.results.election.show', $election);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index'))
            ->assertOk()
            ->assertSee('View official results')
            ->assertSee($resultsUrl, false);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.show', $election))
            ->assertOk()
            ->assertSee('View official results')
            ->assertSee($resultsUrl, false)
            ->assertDontSee('Vote Now');
    }

    public function test_unpublished_closed_election_does_not_link_to_results(): void
    {
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create([
            'title' => 'Unpublished Campus Vote',
            'public_results_published' => false,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.show', $election))
            ->assertOk()
            ->assertSee('Unpublished Campus Vote')
            ->assertSee('Closed')
            ->assertDontSee('View official results');
    }

    public function test_draft_election_is_not_found_for_faculty(): void
    {
        $faculty = User::factory()->faculty()->create();
        $draft = Election::factory()->draft()->create(['title' => 'Hidden Draft']);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.show', $draft))
            ->assertNotFound();
    }
}
