<?php

namespace Tests\Unit\Student;

use App\Enums\DonationStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Donation;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Models\Vote;
use App\Services\Student\StudentStatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentStatisticsServiceTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_eligible_elections_exclude_old_closed_races_the_student_never_saw(): void
    {
        $student = User::factory()->create(['created_at' => now()]);

        Election::factory()->closed()->create([
            'title' => 'Last Year Council',
            'voting_starts_at' => now()->subYear(),
            'voting_ends_at' => now()->subMonths(11),
        ]);

        $stats = app(StudentStatisticsService::class)->forStudent($student);

        $this->assertSame(0, $stats['votingAnalytics']['eligible_elections']);
        $this->assertSame(0, $stats['votingAnalytics']['lifetime_percent']);
        $this->assertSame(0, $stats['achievements']['total_activities']);
        $this->assertSame('New Participant', $stats['achievements']['participation_level']);
        $this->assertSame('Not yet donated', $stats['achievements']['supporter_level']);
        $this->assertNull($stats['achievements']['most_active_month']);
    }

    public function test_open_and_voted_elections_count_toward_personal_turnout(): void
    {
        $student = User::factory()->create(['created_at' => now()->subDay()]);
        $fixture = $this->createElectionBallot(1);

        Election::factory()->closed()->create([
            'voting_starts_at' => now()->subYear(),
            'voting_ends_at' => now()->subMonths(11),
        ]);

        $beforeVote = app(StudentStatisticsService::class)->forStudent($student);
        $this->assertSame(1, $beforeVote['votingAnalytics']['eligible_elections']);
        $this->assertSame(0, $beforeVote['votingAnalytics']['lifetime_percent']);

        Vote::withoutEvents(function () use ($student, $fixture) {
            Vote::query()->create([
                'user_id' => $student->id,
                'election_id' => $fixture['election']->id,
                'election_category_id' => $fixture['categories'][0]->id,
                'candidate_id' => $fixture['candidates'][0]->id,
                'voted_at' => now(),
            ]);
        });

        $afterVote = app(StudentStatisticsService::class)->forStudent($student->fresh());

        $this->assertSame(1, $afterVote['overview']['elections_joined']);
        $this->assertSame(1, $afterVote['overview']['votes_cast']);
        $this->assertSame(1, $afterVote['votingAnalytics']['eligible_elections']);
        $this->assertSame(100, $afterVote['votingAnalytics']['lifetime_percent']);
        $this->assertSame(1, $afterVote['votingAnalytics']['positions_voted']);
        $this->assertSame(1, $afterVote['achievements']['total_activities']);
        $this->assertSame(now()->format('F Y'), $afterVote['achievements']['most_active_month']);
    }

    public function test_fundraising_engagement_ignores_draft_and_hidden_drives(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $visible = $this->makeFundraiser($admin, 'Visible Drive', [
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
        ]);
        $this->makeFundraiser($admin, 'Draft Drive', [
            'status' => FundraiserStatus::Draft,
            'visibility' => FundraiserVisibility::Public,
        ]);
        $this->makeFundraiser($admin, 'Hidden Drive', [
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Hidden,
        ]);

        Donation::withoutEvents(function () use ($student, $visible) {
            Donation::query()->create([
                'fundraiser_id' => $visible->id,
                'user_id' => $student->id,
                'amount' => 100,
                'status' => DonationStatus::Paid,
                'donated_at' => now(),
                'paid_at' => now(),
            ]);
        });

        $stats = app(StudentStatisticsService::class)->forStudent($student->fresh());

        $this->assertSame(1, $stats['overview']['fundraisers_supported']);
        $this->assertSame(1, $stats['donationSummary']['drives_supported']);
        $this->assertSame(100, $stats['engagement']['fundraising']);
        $this->assertSame(1, $stats['achievements']['total_activities']);
        $this->assertSame('Supporter', $stats['achievements']['supporter_level']);
    }

    public function test_supporter_level_rises_with_distinct_paid_drives(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();

        foreach (['Drive A', 'Drive B'] as $title) {
            $drive = $this->makeFundraiser($admin, $title);
            Donation::withoutEvents(function () use ($student, $drive) {
                Donation::query()->create([
                    'fundraiser_id' => $drive->id,
                    'user_id' => $student->id,
                    'amount' => 50,
                    'status' => DonationStatus::Paid,
                    'donated_at' => now(),
                    'paid_at' => now(),
                ]);
            });
        }

        $stats = app(StudentStatisticsService::class)->forStudent($student->fresh());

        $this->assertSame(2, $stats['donationSummary']['drives_supported']);
        $this->assertSame('Active donor', $stats['achievements']['supporter_level']);
    }

    public function test_withdrawn_talent_entries_do_not_count_as_joined(): void
    {
        $student = User::factory()->create();
        $event = TalentEvent::query()->create([
            'election_id' => Election::factory()->active()->create()->id,
            'title' => 'Campus Idol',
            'slug' => 'campus-idol-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Hall',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'published_at' => now()->subHour(),
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'user_id' => $student->id,
            'display_name' => $student->name,
            'performance_title' => 'Solo',
            'status' => TalentEventEntry::STATUS_WITHDRAWN,
            'source' => TalentEventEntry::SOURCE_SELF,
        ]);

        $stats = app(StudentStatisticsService::class)->forStudent($student);

        $this->assertSame(0, $stats['overview']['competitions_joined']);
        $this->assertSame(0, $stats['engagement']['competitions']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeFundraiser(User $admin, string $title, array $overrides = []): Fundraiser
    {
        return Fundraiser::query()->create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'goal_amount' => 5000,
            'amount_raised' => 0,
            'min_donation' => 20,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(10)->toDateString(),
            'created_by' => $admin->id,
        ], $overrides));
    }
}
