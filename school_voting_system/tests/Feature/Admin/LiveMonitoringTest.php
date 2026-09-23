<?php

namespace Tests\Feature\Admin;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Permission;
use App\Models\StaffRole;
use App\Models\TalentEvent;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_election_live_monitoring_page_shows_leaders_by_position_and_time_remaining(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['title' => 'SSC Election 2026']);
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'sort_order' => 1,
        ]);
        $candidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Maria Santos',
        ]);

        Vote::withoutEvents(function () use ($election, $category, $candidate) {
            Vote::query()->create([
                'user_id' => User::factory()->create()->id,
                'election_id' => $election->id,
                'election_category_id' => $category->id,
                'candidate_id' => $candidate->id,
                'voted_at' => now(),
            ]);
        });

        $this->actingAs($admin)
            ->get(route('admin.live.election'))
            ->assertOk()
            ->assertSee('Election Live Monitoring')
            ->assertSee('Leading by position')
            ->assertSee('President')
            ->assertSee('Maria Santos')
            ->assertSee('Voting Ends In')
            ->assertSee('Talent Live Monitoring');
    }

    public function test_election_live_poll_includes_position_leaders_and_countdown(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['title' => 'Poll Council']);
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'Treasurer',
            'sort_order' => 1,
        ]);
        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Waiting Candidate',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.live.election.poll'))
            ->assertOk()
            ->assertJsonPath('cards.0.show_position_leaders', true)
            ->assertJsonPath('cards.0.position_leaders.0.position', 'Treasurer')
            ->assertJsonPath('cards.0.position_leaders.0.display', '—')
            ->assertJsonPath('cards.0.countdown.label', 'Voting Ends In');
    }

    public function test_regular_admin_does_not_see_soft_deleted_election_in_live_monitoring(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Removed Council Race',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.live.election.poll'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Removed Council Race']);

        $election->delete();

        $this->actingAs($admin)
            ->getJson(route('admin.live.election.poll'))
            ->assertOk()
            ->assertJsonMissing(['name' => 'Removed Council Race']);
    }

    public function test_operations_admin_live_polls_include_super_admin_activities(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = $this->makeOperationsAdmin();
        $election = Election::factory()->active()->create([
            'title' => 'Campus Student Council',
            'created_by' => $super->id,
        ]);
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

        $this->actingAs($admin)
            ->get(route('admin.live.election'))
            ->assertOk()
            ->assertSee('Campus Student Council')
            ->assertSee('you can create or manage');

        $this->actingAs($admin)
            ->getJson(route('admin.live.election.poll'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Campus Student Council']);

        $this->actingAs($admin)
            ->get(route('admin.live.talent'))
            ->assertOk()
            ->assertSee('Campus Idol Live')
            ->assertDontSee('No Live Activities');

        $this->actingAs($admin)
            ->getJson(route('admin.live.talent.poll'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Campus Idol Live']);
    }

    public function test_operations_admin_can_pause_super_admin_election_and_talent(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = $this->makeOperationsAdmin();
        $election = Election::factory()->active()->create([
            'title' => 'Campus Student Council',
            'created_by' => $super->id,
        ]);
        $talentEvent = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Campus Idol Live',
            'slug' => 'campus-idol-pause-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(3),
            'published_to_students' => true,
            'created_by' => $super->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.live.election'))
            ->post(route('admin.election.pause', $election))
            ->assertRedirect(route('admin.live.election'));

        $this->assertTrue($election->fresh()->is_paused);

        $this->actingAs($admin)
            ->from(route('admin.live.talent'))
            ->post(route('admin.live.talent.pause', $talentEvent))
            ->assertRedirect(route('admin.live.talent'));

        $this->assertTrue($talentEvent->fresh()->is_paused);
    }

    protected function makeOperationsAdmin(): User
    {
        $permissions = collect(['create_talent_events', 'modify_elections', 'pause_election'])->map(function (string $key) {
            return Permission::query()->firstOrCreate(
                ['key' => $key],
                ['label' => $key, 'category' => 'events'],
            );
        });

        $role = StaffRole::query()->create([
            'name' => 'Operations Admin',
            'slug' => 'ops-live-monitor-'.uniqid(),
            'description' => 'Can create elections and talent events',
            'is_system' => true,
        ]);
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->admin()->create([
            'staff_role_id' => $role->id,
        ]);
    }
}
