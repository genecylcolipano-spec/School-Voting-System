<?php

namespace Tests\Feature\Admin;

use App\Models\AdminAssignment;
use App\Models\Election;
use App\Models\Partylist;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_voting_management_pages_use_unread_notification_count(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->create(['title' => 'Workspace Council', 'created_by' => $super->id]);
        Partylist::factory()->create(['name' => 'Unity Ticket']);

        PortalNotification::query()->create([
            'title' => 'Voting ping',
            'message' => 'Unread for voting management',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Workspace for election details, positions, and candidates')
            ->assertSee(route('admin.elections.edit', $election), false)
            ->assertSee('>Manage<', false)
            ->assertSee('Workspace Council');

        $this->actingAs($super)
            ->get(route('admin.campaigns.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Unity Ticket');

        $this->actingAs($super)
            ->get(route('admin.live.election'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Positions and candidates stay in Elections');
    }

    public function test_super_admin_sees_every_election_and_regular_admin_sees_assigned_only(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $assigned = Election::factory()->create(['title' => 'Assigned Council', 'created_by' => $admin->id]);
        $other = Election::factory()->create(['title' => 'Campus-Wide Ballot']);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $assigned->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($super)
            ->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee('Assigned Council')
            ->assertSee('Campus-Wide Ballot');

        $this->actingAs($admin)
            ->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee('Assigned Council')
            ->assertDontSee('Campus-Wide Ballot');
    }

    public function test_deleting_an_election_confirms_the_election_was_removed(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->create(['title' => 'Disposable Council']);

        $this->actingAs($super)
            ->from(route('admin.elections.index'))
            ->delete(route('admin.elections.destroy', $election))
            ->assertRedirect(route('admin.elections.index'))
            ->assertSessionHas('success', 'Election deleted successfully.');

        $this->assertSoftDeleted($election);
    }
}
