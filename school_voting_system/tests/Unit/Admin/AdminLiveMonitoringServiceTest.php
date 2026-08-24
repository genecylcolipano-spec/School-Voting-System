<?php

namespace Tests\Unit\Admin;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\TalentEvent;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminLiveMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLiveMonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_elections_as_monitoring_cards(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Election::factory()->create([
            'title' => 'Campus Election A',
            'created_by' => $admin->id,
            'status' => ElectionStatus::Draft,
        ]);

        $cards = app(AdminLiveMonitoringService::class)->electionCards($super);

        $this->assertTrue($cards->contains(fn (array $card) => $card['name'] === 'Campus Election A'));
        $this->assertSame($admin->name, $cards->firstWhere('name', 'Campus Election A')['owner_name']);
        $this->assertFalse($cards->firstWhere('name', 'Campus Election A')['has_banner']);
        $this->assertNull($cards->firstWhere('name', 'Campus Election A')['banner_url']);
    }

    public function test_admin_only_sees_created_or_assigned_elections(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $other = User::factory()->create(['role' => UserRole::Admin]);

        Election::factory()->create([
            'title' => 'Mine',
            'created_by' => $admin->id,
            'status' => ElectionStatus::Draft,
        ]);

        Election::factory()->create([
            'title' => 'Theirs',
            'created_by' => $other->id,
            'status' => ElectionStatus::Draft,
        ]);

        $cards = app(AdminLiveMonitoringService::class)->electionCards($admin);

        $this->assertTrue($cards->contains(fn (array $card) => $card['name'] === 'Mine'));
        $this->assertFalse($cards->contains(fn (array $card) => $card['name'] === 'Theirs'));
    }

    public function test_live_election_cards_include_leading_candidates_by_position(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['title' => 'Live Council']);

        $president = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'sort_order' => 1,
        ]);
        $secretary = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'Secretary',
            'sort_order' => 3,
        ]);

        $maria = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $president->id,
            'display_name' => 'Maria Santos',
        ]);
        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $president->id,
            'display_name' => 'Trailing President',
        ]);
        $juan = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $secretary->id,
            'display_name' => 'Juan Dela Cruz',
        ]);
        $tiedSecretary = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $secretary->id,
            'display_name' => 'Ana Reyes',
        ]);

        $this->castVotes($election, $president, $maria, 2);
        $this->castVotes($election, $secretary, $juan, 1);
        $this->castVotes($election, $secretary, $tiedSecretary, 1);

        $card = app(AdminLiveMonitoringService::class)
            ->electionCards($super)
            ->firstWhere('name', 'Live Council');

        $this->assertTrue($card['is_live']);
        $this->assertTrue($card['show_position_leaders']);
        $this->assertSame('Voting Ends In', $card['countdown']['label']);
        $this->assertNotEmpty($card['countdown']['target_at_iso']);

        $this->assertCount(2, $card['position_leaders']);
        $this->assertSame('President', $card['position_leaders'][0]['position']);
        $this->assertSame('Maria Santos', $card['position_leaders'][0]['display']);
        $this->assertSame(2, $card['position_leaders'][0]['votes']);
        $this->assertFalse($card['position_leaders'][0]['tied']);

        $this->assertSame('Secretary', $card['position_leaders'][1]['position']);
        $this->assertTrue($card['position_leaders'][1]['tied']);
        $this->assertSame(1, $card['position_leaders'][1]['votes']);
        $this->assertStringContainsString(' +1', $card['position_leaders'][1]['display']);
    }

    public function test_paused_election_still_shows_position_leaders(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Paused Council',
            'is_paused' => true,
        ]);
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'sort_order' => 1,
        ]);
        $candidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Paused Leader',
        ]);
        $this->castVotes($election, $category, $candidate, 1);

        $card = app(AdminLiveMonitoringService::class)
            ->electionCards($super)
            ->firstWhere('name', 'Paused Council');

        $this->assertSame('voting_paused', $card['status_key']);
        $this->assertTrue($card['show_position_leaders']);
        $this->assertSame('Paused Leader', $card['position_leaders'][0]['display']);
    }

    public function test_scheduled_elections_omit_position_leaders_but_include_start_countdown(): void
    {
        $super = User::factory()->superAdmin()->create();
        Election::factory()->draft()->create(['title' => 'Upcoming Council']);

        $card = app(AdminLiveMonitoringService::class)
            ->electionCards($super)
            ->firstWhere('name', 'Upcoming Council');

        $this->assertFalse($card['show_position_leaders']);
        $this->assertSame([], $card['position_leaders']);
        $this->assertSame('Voting Starts In', $card['countdown']['label']);
        $this->assertSame('before_start', $card['countdown']['phase']);
    }

    public function test_talent_cards_include_voting_countdown(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create();

        TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Campus Idol Live',
            'slug' => 'campus-idol-live-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(3),
            'published_to_students' => true,
            'created_by' => $super->id,
        ]);

        $card = app(AdminLiveMonitoringService::class)
            ->talentCards($super)
            ->firstWhere('name', 'Campus Idol Live');

        $this->assertTrue($card['is_live']);
        $this->assertSame('Voting Ends In', $card['countdown']['label']);
        $this->assertNotEmpty($card['countdown']['target_at_iso']);
    }

    protected function castVotes(Election $election, ElectionCategory $category, Candidate $candidate, int $count): void
    {
        Vote::withoutEvents(function () use ($election, $category, $candidate, $count) {
            for ($i = 0; $i < $count; $i++) {
                Vote::query()->create([
                    'user_id' => User::factory()->create()->id,
                    'election_id' => $election->id,
                    'election_category_id' => $category->id,
                    'candidate_id' => $candidate->id,
                    'voted_at' => now(),
                ]);
            }
        });
    }
}
