<?php

namespace Tests\Unit\Faculty;

use App\Enums\ElectionStatus;
use App\Enums\EventStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Faculty\FacultyUpcomingActivitiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyUpcomingActivitiesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FacultyUpcomingActivitiesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FacultyUpcomingActivitiesService::class);
    }

    public function test_open_election_uses_faculty_view_details_not_vote(): void
    {
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Open Faculty Ballot',
        ]);

        $row = $this->service->forDashboard($faculty)->firstWhere('title', 'Open Faculty Ballot');

        $this->assertNotNull($row);
        $this->assertSame('View details', $row['action_label']);
        $this->assertSame('secondary', $row['action_style']);
        $this->assertFalse($row['action_disabled']);
        $this->assertSame(route('faculty.elections.show', $election), $row['action_url']);
        $this->assertStringNotContainsString('/vote', $row['action_url']);
    }

    public function test_published_election_results_link_to_faculty_results(): void
    {
        $election = Election::factory()->closed()->create([
            'title' => 'Published Faculty Ballot',
            'public_results_published' => true,
            'results_published_at' => now()->subHour(),
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Published Faculty Ballot');

        $this->assertNotNull($row);
        $this->assertSame('View Results', $row['action_label']);
        $this->assertSame(route('faculty.results.election.show', $election), $row['action_url']);
    }

    public function test_closed_election_awaiting_results_stays_viewable(): void
    {
        $election = Election::factory()->closed()->create([
            'title' => 'Finished Faculty Ballot',
            'public_results_published' => false,
            'voting_ends_at' => now()->subDay(),
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Finished Faculty Ballot');

        $this->assertNotNull($row);
        $this->assertSame('Results Pending', $row['status_label']);
        $this->assertSame('View details', $row['action_label']);
        $this->assertFalse($row['action_disabled']);
        $this->assertSame(route('faculty.elections.show', $election), $row['action_url']);
    }

    public function test_archived_election_without_results_is_hidden(): void
    {
        Election::factory()->create([
            'title' => 'Archived Faculty Ballot',
            'status' => ElectionStatus::Archived,
            'public_results_published' => false,
            'voting_starts_at' => now()->subDays(10),
            'voting_ends_at' => now()->subDays(8),
        ]);

        $rows = $this->service->forDashboard();

        $this->assertTrue($rows->where('title', 'Archived Faculty Ballot')->isEmpty());
    }

    public function test_school_event_links_to_faculty_event_show(): void
    {
        $admin = User::factory()->admin()->create();

        Event::query()->create([
            'title' => 'Faculty Assembly',
            'slug' => 'faculty-assembly',
            'event_date' => now()->addDays(4),
            'venue' => 'AVR',
            'status' => EventStatus::Scheduled,
            'created_by' => $admin->id,
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Faculty Assembly');

        $this->assertNotNull($row);
        $this->assertSame('View details', $row['action_label']);
        $this->assertSame(route('faculty.events.show', 'faculty-assembly'), $row['action_url']);
    }

    public function test_open_talent_voting_uses_view_details_not_vote(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create();
        $talent = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Open Faculty Showcase',
            'slug' => 'open-faculty-showcase',
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Open Faculty Showcase');

        $this->assertNotNull($row);
        $this->assertSame('View details', $row['action_label']);
        $this->assertSame('secondary', $row['action_style']);
        $this->assertFalse($row['action_disabled']);
        $this->assertSame(route('faculty.talent.show', $talent), $row['action_url']);
    }

    public function test_published_talent_results_link_to_faculty_results(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create();
        $talent = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Published Faculty Showcase',
            'slug' => 'published-faculty-showcase',
            'event_date' => now()->subDay(),
            'status' => TalentEventStatus::ResultsPublished,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subDays(3),
            'voting_ends_at' => now()->subDay(),
            'published_to_students' => true,
            'results_published_at' => now()->subHour(),
            'created_by' => $admin->id,
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Published Faculty Showcase');

        $this->assertNotNull($row);
        $this->assertSame('View Results', $row['action_label']);
        $this->assertSame(route('faculty.results.talent.show', $talent), $row['action_url']);
    }

    public function test_active_fundraiser_uses_view_details_not_donate(): void
    {
        $admin = User::factory()->admin()->create();
        $fundraiser = Fundraiser::query()->create([
            'title' => 'Faculty ICT Drive',
            'slug' => 'faculty-ict-drive',
            'goal_amount' => 5000,
            'amount_raised' => 100,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(5)->toDateString(),
            'created_by' => $admin->id,
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Faculty ICT Drive');

        $this->assertNotNull($row);
        $this->assertSame('View details', $row['action_label']);
        $this->assertSame('secondary', $row['action_style']);
        $this->assertSame(route('faculty.fundraising.show', $fundraiser), $row['action_url']);
        $this->assertNotSame('Donate', $row['action_label']);
    }
}
