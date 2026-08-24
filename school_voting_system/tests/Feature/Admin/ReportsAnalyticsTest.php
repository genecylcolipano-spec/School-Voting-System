<?php

namespace Tests\Feature\Admin;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Enums\ElectionStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Candidate;
use App\Models\Donation;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Fundraiser;
use App\Models\Partylist;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportsAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_election_report_defaults_to_closed_over_draft(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->draft()->create(['title' => 'Draft Council']);
        $closed = Election::factory()->closed()->create([
            'title' => 'Finished Council',
            'public_results_published' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Finished Council')
            ->assertDontSee('No election report available');

        $resolved = app(AdminScopeService::class)->resolveReportElection($admin, null);

        $this->assertSame($closed->id, $resolved?->id);
    }

    public function test_analytics_defaults_to_active_when_a_live_election_exists(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->closed()->create(['title' => 'Last Year']);
        $active = Election::factory()->active()->create(['title' => 'Live Council']);

        $this->actingAs($admin)
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Live Council');

        $resolved = app(AdminScopeService::class)->resolveReportElection($admin, null, preferClosed: false);

        $this->assertSame($active->id, $resolved?->id);
    }

    public function test_super_admin_can_open_a_closed_election_report_by_picker(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->active()->create(['title' => 'Live Council']);
        $closed = Election::factory()->closed()->create(['title' => 'Finished Council']);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['election' => $closed->id]))
            ->assertOk()
            ->assertSee('Finished Council');
    }

    public function test_regular_admin_can_report_on_an_election_they_created(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->closed()->create([
            'title' => 'Created By Admin',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Created By Admin');

        $this->assertSame(
            $election->id,
            app(AdminScopeService::class)->resolveReportElection($admin, null)?->id,
        );
    }

    public function test_regular_admin_cannot_open_another_admins_election_report(): void
    {
        $admin = User::factory()->admin()->create();
        $other = Election::factory()->closed()->create([
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['election' => $other->id]))
            ->assertForbidden();
    }

    public function test_talent_report_lists_actual_winners_not_planned_count(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition([
            'title' => 'Lakan Night',
            'number_of_winners' => 3,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
        ]);
        $winner = $this->makeEntry($event, 'Ana Winner');
        $this->makeEntry($event, 'Ben Runner');
        TalentEventVote::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $winner->id,
            'user_id' => User::factory()->create()->id,
            'voted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.talent'))
            ->assertOk()
            ->assertSee('Lakan Night')
            ->assertSee('Ana Winner');
    }

    public function test_fundraising_report_is_paid_only_and_scoped_to_creator(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        $mine = $this->makeFundraiser($admin, 'My Drive');
        $theirs = $this->makeFundraiser($other, 'Other Drive');

        $paid = Donation::record(User::factory()->create(), $mine, 100, [
            'payment_method' => DonationPaymentMethod::Cash,
        ]);
        $paid->markPaid();

        Donation::record(User::factory()->create(), $mine, 50, [
            'payment_method' => DonationPaymentMethod::Cash,
            'status' => DonationStatus::Pending,
        ]);

        Donation::record(User::factory()->create(), $theirs, 999, [
            'payment_method' => DonationPaymentMethod::Cash,
        ])->markPaid();

        $this->actingAs($admin)
            ->get(route('admin.reports.fundraising'))
            ->assertOk()
            ->assertSee('My Drive')
            ->assertDontSee('Other Drive')
            ->assertSee('₱100.00')
            ->assertDontSee('₱999.00');

        $this->actingAs($admin)
            ->get(route('admin.reports.fundraising.export', ['format' => 'csv']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_campaign_performance_treats_tied_candidates_as_winners(): void
    {
        $election = Election::factory()->active()->create();
        $category = ElectionCategory::factory()->create(['election_id' => $election->id]);
        $campaignA = Partylist::factory()->create();
        $campaignB = Partylist::factory()->create();
        $election->partylists()->sync([$campaignA->id, $campaignB->id]);

        $candidateA = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => $campaignA->id,
        ]);
        $candidateB = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => $campaignB->id,
        ]);

        Vote::castBallot(User::factory()->create(), $candidateA);
        Vote::castBallot(User::factory()->create(), $candidateB);

        $admin = User::factory()->superAdmin()->create();
        $performance = collect(app(AdminAnalyticsService::class)->campaignPerformance($admin))
            ->keyBy('partylist_id');

        $this->assertSame(1, $performance[$campaignA->id]['winning_candidates']);
        $this->assertSame(1, $performance[$campaignB->id]['winning_candidates']);
    }

    public function test_analytics_participation_runs_on_sqlite(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create();
        $category = ElectionCategory::factory()->create(['election_id' => $election->id]);
        $candidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
        ]);
        Vote::castBallot(User::factory()->create(), $candidate);

        $report = app(AdminAnalyticsService::class)->fullReport($admin, $election);

        $this->assertCount(12, $report['participation']['values']);
        $this->assertGreaterThan(0, max($report['participation']['values']));
        $this->assertSame($election->id, $report['election_id']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Reports Showcase',
            'slug' => 'reports-showcase-'.uniqid(),
            'event_date' => now()->subDay(),
            'venue' => 'Online',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHour(),
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

    protected function makeFundraiser(User $admin, string $title): Fundraiser
    {
        return Fundraiser::query()->create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'goal_amount' => 5000,
            'amount_raised' => 0,
            'min_donation' => 20,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'accept_gcash' => true,
            'accept_maya' => true,
            'accept_qrph' => true,
            'accept_cash' => true,
            'accept_bank_transfer' => true,
            'allow_anonymous' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(10)->toDateString(),
            'created_by' => $admin->id,
        ]);
    }
}
