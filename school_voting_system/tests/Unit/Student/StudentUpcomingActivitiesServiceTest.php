<?php

namespace Tests\Unit\Student;

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
use App\Services\Student\StudentUpcomingActivitiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentUpcomingActivitiesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentUpcomingActivitiesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StudentUpcomingActivitiesService::class);
    }

    public function test_empty_dashboard_returns_empty_collection(): void
    {
        $rows = $this->service->forDashboard();

        $this->assertTrue($rows->isEmpty());
    }

    public function test_priority_orders_registration_before_voting_before_scheduled(): void
    {
        $admin = User::factory()->admin()->create();

        $scheduledElection = Election::factory()->create([
            'title' => 'Scheduled Council Election',
            'status' => ElectionStatus::Active,
            'voting_starts_at' => now()->addDays(5),
            'voting_ends_at' => now()->addDays(7),
        ]);

        $votingElection = Election::factory()->active()->create([
            'title' => 'Open Ballot Election',
        ]);

        TalentEvent::query()->create([
            'election_id' => $scheduledElection->id,
            'title' => 'Open Registration Showcase',
            'slug' => 'open-registration-showcase',
            'event_date' => now()->addDays(10),
            'status' => TalentEventStatus::EntriesOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDay(),
            'voting_starts_at' => now()->addDays(3),
            'voting_ends_at' => now()->addDays(5),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        Event::query()->create([
            'title' => 'Future Intramurals',
            'slug' => 'future-intramurals',
            'event_date' => now()->addDays(8),
            'venue' => 'Main Gym',
            'status' => EventStatus::Scheduled,
            'created_by' => $admin->id,
        ]);

        $rows = $this->service->forDashboard();

        $this->assertGreaterThanOrEqual(3, $rows->count());
        $this->assertSame('Open Registration Showcase', $rows->first()['title']);
        $this->assertSame('View Details', $rows->first()['action_label']);
        $this->assertSame('secondary', $rows->first()['action_style']);

        $votingRow = $rows->firstWhere('title', 'Open Ballot Election');
        $this->assertNotNull($votingRow);
        $this->assertSame('Vote', $votingRow['action_label']);
        $this->assertSame('primary', $votingRow['action_style']);
        $this->assertSame('voting_open', $votingRow['status_key']);

        $priorities = $rows->pluck('sort_priority')->all();
        $sorted = $priorities;
        sort($sorted);
        $this->assertSame($sorted, $priorities);
    }

    public function test_unpublished_talent_and_draft_fundraiser_are_hidden(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create();

        TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Hidden Draft Competition',
            'slug' => 'hidden-draft-competition',
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'published_to_students' => false,
            'created_by' => $admin->id,
        ]);

        Fundraiser::query()->create([
            'title' => 'Draft Drive',
            'slug' => 'draft-drive-'.Str::random(6),
            'goal_amount' => 1000,
            'amount_raised' => 0,
            'status' => FundraiserStatus::Draft,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(10)->toDateString(),
            'created_by' => $admin->id,
        ]);

        $rows = $this->service->forDashboard();

        $this->assertTrue($rows->where('title', 'Hidden Draft Competition')->isEmpty());
        $this->assertTrue($rows->where('title', 'Draft Drive')->isEmpty());
    }

    public function test_active_fundraiser_shows_donate_action(): void
    {
        $admin = User::factory()->admin()->create();

        Fundraiser::query()->create([
            'title' => 'ICT Fundraising Drive',
            'slug' => 'ict-fundraising-drive',
            'goal_amount' => 5000,
            'amount_raised' => 100,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(5)->toDateString(),
            'created_by' => $admin->id,
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'ICT Fundraising Drive');

        $this->assertNotNull($row);
        $this->assertSame('Fundraising', $row['category']);
        $this->assertSame('Donate', $row['action_label']);
        $this->assertSame('primary', $row['action_style']);
        $this->assertFalse($row['action_disabled']);
        $this->assertNotEmpty($row['banner_url']);
        $this->assertStringContainsString('activity-covers/fundraising.svg', $row['banner_url']);
    }

    public function test_election_without_banner_uses_category_cover(): void
    {
        Election::factory()->active()->create([
            'title' => 'Cover Fallback Election',
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Cover Fallback Election');

        $this->assertNotNull($row);
        $this->assertStringContainsString('activity-covers/election.svg', $row['banner_url']);
    }

    public function test_closed_election_awaiting_results_is_disabled(): void
    {
        Election::factory()->closed()->create([
            'title' => 'Finished Election',
            'public_results_published' => false,
            'voting_ends_at' => now()->subDay(),
        ]);

        $row = $this->service->forDashboard()->firstWhere('title', 'Finished Election');

        $this->assertNotNull($row);
        $this->assertSame('Results Pending', $row['status_label']);
        $this->assertSame('Under Review', $row['action_label']);
        $this->assertSame('disabled', $row['action_style']);
        $this->assertTrue($row['action_disabled']);
        $this->assertNull($row['action_url']);
    }
}
