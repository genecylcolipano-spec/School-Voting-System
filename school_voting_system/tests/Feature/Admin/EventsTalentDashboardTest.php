<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\AdminAssignment;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsTalentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_sees_own_talent_competitions_even_when_election_id_differs(): void
    {
        $admin = User::factory()->admin()->create();
        $assigned = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);
        $previous = Election::factory()->create([
            'status' => ElectionStatus::Closed,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $assigned->id,
            'assigned_by' => $admin->id,
        ]);

        $ownCompetition = $this->makeCompetition($previous, $admin, 'Campus Idol');
        $assignedCompetition = $this->makeCompetition(
            $assigned,
            User::factory()->admin()->create(),
            'Assigned Quiz Bowl',
        );
        $outOfScope = $this->makeCompetition(
            $previous,
            User::factory()->admin()->create(),
            'Other School Showcase',
        );

        $this->actingAs($admin)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('Campus Idol')
            ->assertSee('Assigned Quiz Bowl')
            ->assertDontSee('Other School Showcase')
            ->assertDontSee('No talent events yet.');

        $this->assertNotNull($ownCompetition->id);
        $this->assertNotNull($assignedCompetition->id);
        $this->assertNotNull($outOfScope->id);
    }

    public function test_super_admin_sees_all_talent_competitions_on_events_dashboard(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->create();
        $this->makeCompetition($election, User::factory()->admin()->create(), 'Hidden From Regular Filter');

        $this->actingAs($super)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('Hidden From Regular Filter');
    }

    protected function makeCompetition(Election $election, User $creator, string $title): TalentEvent
    {
        return TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Online',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => $creator->id,
        ]);
    }
}
