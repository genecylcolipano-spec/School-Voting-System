<?php

namespace Tests\Feature\Faculty;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Services\Talent\TalentJudgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class FacultyResultsTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_past_assigned_unpublished_competition_stays_under_review(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeEndedCompetition([
            'title' => 'Unpublished Night',
            'slug' => 'unpublished-night',
        ]);
        $entry = $this->makeEntry($event, 'Secret Winner');
        $this->castVotes($event, $entry, 3);

        app(TalentJudgingService::class)->assignJudge($event, $faculty, $admin);

        $this->actingAs($faculty)
            ->get(route('faculty.judging.index', ['filter' => 'past']))
            ->assertOk()
            ->assertSee('Unpublished Night')
            ->assertSee('Under administrator review')
            ->assertDontSee('View official results')
            ->assertDontSee('Secret Winner');

        $this->actingAs($faculty)
            ->get(route('faculty.results.talent.show', $event))
            ->assertNotFound();

        $this->actingAs($faculty)
            ->get(route('faculty.results.index'))
            ->assertOk()
            ->assertDontSee('Unpublished Night')
            ->assertDontSee('Secret Winner');
    }

    public function test_past_assigned_published_competition_shows_official_results(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeEndedCompetition([
            'title' => 'Published Night',
            'slug' => 'published-night',
        ]);
        $winner = $this->makeEntry($event, 'Official Winner');
        $runner = $this->makeEntry($event, 'Official Runner');
        $this->castVotes($event, $winner, 4);
        $this->castVotes($event, $runner, 1);

        app(TalentJudgingService::class)->assignJudge($event, $faculty, $admin);

        $event->forceFill([
            'status' => TalentEventStatus::ResultsPublished,
            'results_published_at' => now()->subHour(),
        ])->save();

        $this->actingAs($faculty)
            ->get(route('faculty.judging.index', ['filter' => 'past']))
            ->assertOk()
            ->assertSee('Published Night')
            ->assertSee('View official results')
            ->assertDontSee('Under administrator review');

        $this->actingAs($faculty)
            ->get(route('faculty.results.talent.show', [$event, 'from' => 'assigned']))
            ->assertOk()
            ->assertSee('Official Results')
            ->assertSee('Official Winner')
            ->assertSee('Final Rankings')
            ->assertSee('Assigned competitions')
            ->assertDontSee('Vote Now');
    }

    public function test_unassigned_faculty_can_view_published_campus_results(): void
    {
        $faculty = User::factory()->faculty()->create();
        $event = $this->makePublishedCompetition([
            'title' => 'Campus Talent Finals',
            'slug' => 'campus-talent-finals',
        ]);
        $winner = $this->makeEntry($event, 'Campus Champion');
        $this->castVotes($event, $winner, 2);

        ['election' => $election, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $election->forceFill([
            'title' => 'Campus Student Council',
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subDay(),
            'public_results_published' => true,
            'results_published_at' => now()->subHour(),
        ])->save();

        $openElection = Election::factory()->active()->create([
            'title' => 'Still Open Election',
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.results.index'))
            ->assertOk()
            ->assertSee('Results')
            ->assertSee('Campus Talent Finals')
            ->assertSee('Campus Student Council')
            ->assertSee('View Results')
            ->assertDontSee('Still Open Election')
            ->assertDontSee('Vote Now');

        $this->actingAs($faculty)
            ->get(route('faculty.results.talent.show', $event))
            ->assertOk()
            ->assertSee('Campus Champion')
            ->assertSee('All Results');

        $this->actingAs($faculty)
            ->get(route('faculty.results.election.show', $election))
            ->assertOk()
            ->assertSee('Full Rankings')
            ->assertSee($candidates[0]->display_name);

        $this->actingAs($faculty)
            ->get(route('faculty.results.election.show', $openElection))
            ->assertNotFound();
    }

    public function test_unpublished_election_is_hidden_from_faculty_results(): void
    {
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create([
            'title' => 'Closed Unpublished Election',
            'public_results_published' => false,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.results.index'))
            ->assertOk()
            ->assertDontSee('Closed Unpublished Election');

        $this->actingAs($faculty)
            ->get(route('faculty.results.election.show', $election))
            ->assertNotFound();
    }

    public function test_student_cannot_open_faculty_results_routes(): void
    {
        $student = User::factory()->create();
        $event = $this->makePublishedCompetition();

        $this->actingAs($student)
            ->get(route('faculty.results.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('faculty.results.talent.show', $event))
            ->assertForbidden();
    }

    protected function withPasskey(User $faculty): User
    {
        $passkey = new Passkey([
            'name' => 'Test Device',
            'credential_id' => 'cred-'.$faculty->id.'-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
        ]);
        $passkey->user_id = $faculty->id;
        $passkey->save();

        return $faculty;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeEndedCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeCompetition(array_merge([
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => null,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makePublishedCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeCompetition(array_merge([
            'status' => TalentEventStatus::ResultsPublished,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => now()->subHour(),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Faculty Results Night',
            'slug' => 'faculty-results-'.uniqid(),
            'event_date' => now()->subDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function makeEntry(TalentEvent $event, string $name): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => $name,
            'performance_title' => $name.' Act',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
        ]);
    }

    protected function castVotes(TalentEvent $event, TalentEventEntry $entry, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            TalentEventVote::query()->create([
                'talent_event_id' => $event->id,
                'talent_event_entry_id' => $entry->id,
                'user_id' => User::factory()->create()->id,
                'voted_at' => now(),
            ]);
        }
    }
}
