<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Enums\StudentStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Fundraiser;
use App\Models\PasskeyRecoveryRequest;
use App\Models\PortalNotification;
use App\Models\TalentEvent;
use App\Models\User;
use App\Models\Vote;
use App\Services\SuperAdmin\SuperAdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminDashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_search_returns_links_for_accounts_and_elections(): void
    {
        $super = User::factory()->superAdmin()->create(['name' => 'Chief Search']);
        $student = User::factory()->create(['name' => 'Search Student']);
        $election = Election::factory()->create(['title' => 'Searchable Council']);

        $this->actingAs($super)
            ->getJson(route('super-admin.search', ['q' => 'Search']))
            ->assertOk()
            ->assertJsonFragment(['url' => route('admin.students.show', $student)])
            ->assertJsonFragment(['url' => route('admin.elections.edit', $election)]);
    }

    public function test_dashboard_uses_unread_notification_count_not_recovery_count(): void
    {
        $super = User::factory()->superAdmin()->create();
        PortalNotification::query()->create([
            'title' => 'Overview ping',
            'message' => 'Unread for the bell',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('No backups yet')
            ->assertSee('System Attention Needed')
            ->assertSee('No live election.');
    }

    public function test_portal_accounts_include_faculty_and_activity_snapshot(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create(['name' => 'Prof Snapshot']);
        $this->makeCompetition($super, 'Open Showcase Night');
        $this->makeFundraiser($super, 'Library Drive');

        $this->actingAs($super)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Prof Snapshot')
            ->assertSee('Open Showcase Night')
            ->assertSee('Library Drive')
            ->assertSee('Live election')
            ->assertSee('Participation Growth (Events/Voting)')
            ->assertSee('Donation/Fundraising History');
    }

    public function test_live_votes_and_turnout_use_the_open_election_only(): void
    {
        $super = User::factory()->superAdmin()->create();
        $eligible = User::factory()->create(['student_status' => StudentStatus::Enrolled]);
        User::factory()->create(['student_status' => StudentStatus::Enrolled]);

        $live = Election::factory()->active()->create([
            'title' => 'Live Council',
            'created_by' => $super->id,
        ]);
        $old = Election::factory()->closed()->create([
            'title' => 'Old Council',
            'created_by' => $super->id,
        ]);
        $category = ElectionCategory::factory()->create(['election_id' => $live->id]);
        $candidate = Candidate::factory()->create([
            'election_id' => $live->id,
            'election_category_id' => $category->id,
        ]);
        $oldCategory = ElectionCategory::factory()->create(['election_id' => $old->id]);
        $oldCandidate = Candidate::factory()->create([
            'election_id' => $old->id,
            'election_category_id' => $oldCategory->id,
        ]);

        Vote::castBallot($eligible, $candidate);

        Vote::withoutEvents(function () use ($eligible, $old, $oldCategory, $oldCandidate) {
            Vote::query()->create([
                'user_id' => $eligible->id,
                'election_id' => $old->id,
                'election_category_id' => $oldCategory->id,
                'candidate_id' => $oldCandidate->id,
                'voted_at' => now()->subDays(2),
            ]);
        });

        $stats = app(SuperAdminDashboardService::class)->statistics();
        $this->assertSame('Live Council', $stats['election_scope']);
        $this->assertSame(1, $stats['total_votes']);
        $this->assertSame(1, $stats['voted_students']);
        $this->assertSame(2, $stats['eligible_students']);
        $this->assertSame(50.0, $stats['voter_turnout']);

        $this->actingAs($super)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Live Council')
            ->assertSee('50% turnout')
            ->assertSee(route('admin.elections.edit', $live), false)
            ->assertDontSee('Votes and turnout on this dashboard are for the live election only.')
            ->assertSee('Scope: Live Council')
            ->assertSee('50%');
    }

    public function test_live_votes_and_turnout_include_all_open_elections(): void
    {
        $super = User::factory()->superAdmin()->create();
        $firstVoter = User::factory()->create(['student_status' => StudentStatus::Enrolled]);
        $secondVoter = User::factory()->create(['student_status' => StudentStatus::Enrolled]);

        $first = Election::factory()->active()->create(['title' => 'Council A', 'created_by' => $super->id]);
        $second = Election::factory()->active()->create(['title' => 'Council B', 'created_by' => $super->id]);

        $firstCategory = ElectionCategory::factory()->create(['election_id' => $first->id]);
        $secondCategory = ElectionCategory::factory()->create(['election_id' => $second->id]);
        $firstCandidate = Candidate::factory()->create([
            'election_id' => $first->id,
            'election_category_id' => $firstCategory->id,
        ]);
        $secondCandidate = Candidate::factory()->create([
            'election_id' => $second->id,
            'election_category_id' => $secondCategory->id,
        ]);

        Vote::castBallot($firstVoter, $firstCandidate);
        Vote::castBallot($secondVoter, $secondCandidate);

        $stats = app(SuperAdminDashboardService::class)->statistics();
        $this->assertSame('2 live elections', $stats['election_scope']);
        $this->assertSame(2, $stats['total_votes']);
        $this->assertSame(2, $stats['voted_students']);
        $this->assertSame(100.0, $stats['voter_turnout']);
    }

    public function test_portal_account_search_matches_phone(): void
    {
        $super = User::factory()->superAdmin()->create();
        User::factory()->create([
            'name' => 'Phone Student',
            'phone' => '09171234567',
        ]);

        $this->actingAs($super)
            ->get(route('super-admin.dashboard', ['portal_q' => '09171234567']))
            ->assertOk()
            ->assertSee('Phone Student');
    }

    public function test_draft_election_only_shows_open_action_and_rejects_pause(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->draft()->create(['title' => 'Draft Only Election']);

        $html = $this->actingAs($super)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Draft Only Election')
            ->assertSee(route('admin.elections.edit', $election), false)
            ->assertSee('Positions and candidates are in Voting Management')
            ->assertSee('Live charts and election exports are in Reports', false)
            ->getContent();

        $this->assertStringContainsString('>Open<', $html);
        $this->assertStringNotContainsString('name="action" value="pause"', $html);

        $this->actingAs($super)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.elections.action', $election), ['action' => 'pause'])
            ->assertRedirect(route('super-admin.dashboard'))
            ->assertSessionHas('error', 'That action is not available for this election.');
    }

    public function test_dashboard_links_to_recovery_queue_instead_of_embedding_it(): void
    {
        $super = User::factory()->superAdmin()->create();
        User::factory()->faculty()->create([
            'account_id' => 'STU-REC-1',
            'email' => 'faculty.onfile@example.com',
        ]);

        PasskeyRecoveryRequest::query()->create([
            'account_id' => 'STU-REC-1',
            'email' => 'stuck.recovery@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $this->actingAs($super)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending Recovery')
            ->assertSee('data-pending-recovery-stat', false)
            ->assertSee(route('admin.recovery.index'), false)
            ->assertDontSee('Passkey Recovery Requests')
            ->assertDontSee('stuck.recovery@example.com')
            ->assertDontSee('Generate enrollment link');

        $this->actingAs($super)
            ->get(route('admin.recovery.index'))
            ->assertOk()
            ->assertSee('Passkey Recovery Requests')
            ->assertSee('stuck.recovery@example.com')
            ->assertSee('Generate enrollment link');
    }

    protected function makeCompetition(User $creator, string $title): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => $creator->id,
        ]);
    }

    protected function makeFundraiser(User $creator, string $title): Fundraiser
    {
        return Fundraiser::query()->create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'goal_amount' => 5000,
            'amount_raised' => 1200,
            'min_donation' => 20,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(10)->toDateString(),
            'created_by' => $creator->id,
        ]);
    }
}
