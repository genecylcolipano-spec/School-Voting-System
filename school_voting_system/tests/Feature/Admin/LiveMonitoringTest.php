<?php

namespace Tests\Feature\Admin;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
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
}
