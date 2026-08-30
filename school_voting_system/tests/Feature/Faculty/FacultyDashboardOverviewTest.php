<?php

namespace Tests\Feature\Faculty;

use App\Enums\EventStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeRole;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\Event;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Talent\TalentJudgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyDashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_competitions_card_counts_all_assignments_and_previews_six(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        for ($i = 1; $i <= 7; $i++) {
            $event = $this->makeCompetition([
                'title' => "Showcase {$i}",
                'slug' => "showcase-{$i}",
            ]);
            $judging->assignJudge($event, $faculty, $admin, TalentJudgeRole::Judge);
        }

        $response = $this->actingAs($faculty)->get(route('faculty.dashboard'));

        $response->assertOk();
        $response->assertViewHas('assignedCompetitionsCount', 7);
        $response->assertViewHas('assignedCompetitions', fn ($competitions) => $competitions->count() === 6);
        $response->assertSee('Open Judging');
        $response->assertDontSee('View Competition');
    }

    public function test_dashboard_assigned_count_excludes_past_assignments(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        $current = $this->makeCompetition([
            'title' => 'Live Showcase',
            'slug' => 'live-showcase',
        ]);
        $past = $this->makeCompetition([
            'title' => 'Finished Showcase',
            'slug' => 'finished-showcase',
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $judging->assignJudge($current, $faculty, $admin);
        $judging->assignJudge($past, $faculty, $admin);

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertViewHas('assignedCompetitionsCount', 1)
            ->assertSee('Live Showcase')
            ->assertDontSee('Finished Showcase');
    }

    public function test_overview_cards_remain_linked_when_counts_are_zero(): void
    {
        $faculty = User::factory()->faculty()->create();

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertViewHas('openElectionsCount', 0)
            ->assertViewHas('upcomingEventsCount', 0)
            ->assertViewHas('assignedCompetitionsCount', 0)
            ->assertSee(route('faculty.elections.index', ['filter' => 'open']), false)
            ->assertSee(route('faculty.events.index', ['filter' => 'upcoming']), false)
            ->assertSee(route('faculty.judging.index', ['filter' => 'current']), false)
            ->assertSee(route('faculty.fundraising.index'), false)
            ->assertViewHas('activeFundraisersCount', 0);
    }

    public function test_overview_cards_stay_clickable_at_zero_and_match_filtered_lists(): void
    {
        $faculty = User::factory()->faculty()->create();
        $open = Election::factory()->active()->create(['title' => 'Open Campus Vote']);
        $closed = Election::factory()->closed()->create(['title' => 'Closed Campus Vote']);
        $upcoming = $this->makeSchoolEvent([
            'title' => 'Upcoming Assembly',
            'slug' => 'upcoming-assembly',
            'event_date' => now()->addDays(3),
            'status' => EventStatus::Scheduled,
        ]);
        $past = $this->makeSchoolEvent([
            'title' => 'Past Intramurals',
            'slug' => 'past-intramurals',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Completed,
        ]);

        $dashboard = $this->actingAs($faculty)->get(route('faculty.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertViewHas('openElectionsCount', 1);
        $dashboard->assertViewHas('upcomingEventsCount', 1);
        $dashboard->assertViewHas('assignedCompetitionsCount', 0);
        $dashboard->assertSee(route('faculty.elections.index', ['filter' => 'open']), false);
        $dashboard->assertSee(route('faculty.events.index', ['filter' => 'upcoming']), false);
        $dashboard->assertSee(route('faculty.judging.index', ['filter' => 'current']), false);
        $dashboard->assertSee(route('faculty.fundraising.index'), false);
        $dashboard->assertViewHas('activeFundraisersCount', 0);

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index', ['filter' => 'open']))
            ->assertOk()
            ->assertSee($open->title)
            ->assertDontSee($closed->title)
            ->assertSee('Showing elections currently open for voting')
            ->assertSee('All')
            ->assertSee('Open')
            ->assertDontSee('Show all elections');

        $this->actingAs($faculty)
            ->get(route('faculty.elections.index'))
            ->assertOk()
            ->assertSee($open->title)
            ->assertSee($closed->title);

        $this->actingAs($faculty)
            ->get(route('faculty.events.index', ['filter' => 'upcoming']))
            ->assertOk()
            ->assertSee($upcoming->title)
            ->assertDontSee($past->title)
            ->assertSee('Showing upcoming school events')
            ->assertSee('All')
            ->assertSee('Upcoming')
            ->assertDontSee('Show all events');

        $this->actingAs($faculty)
            ->get(route('faculty.events.index'))
            ->assertOk()
            ->assertSee($upcoming->title)
            ->assertSee($past->title);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Campus Talent Night',
            'slug' => 'campus-talent-night-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::JudgesOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeSchoolEvent(array $overrides = []): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'School Event',
            'slug' => 'school-event-'.uniqid(),
            'event_date' => now()->addDays(2),
            'venue' => 'Auditorium',
            'status' => EventStatus::Scheduled,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
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
}
