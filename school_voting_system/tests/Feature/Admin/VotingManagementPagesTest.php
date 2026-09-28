<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Models\AdminAssignment;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Partylist;
use App\Models\PortalNotification;
use App\Models\User;
use App\Models\Vote;
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
            ->assertSee('Create, configure, archive, and duplicate elections')
            ->assertSee(route('admin.elections.edit', $election), false)
            ->assertSee(route('admin.elections.show', $election), false)
            ->assertSee('>View<', false)
            ->assertSee('>Edit<', false)
            ->assertSee('>Duplicate<', false)
            ->assertSee('>Archive<', false)
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

    public function test_super_admin_can_view_duplicate_and_archive_an_election(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['title' => 'Source Council']);
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
        ]);
        $candidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Source Candidate',
        ]);
        Vote::castBallot(User::factory()->create(), $candidate);

        $this->actingAs($super)
            ->get(route('admin.elections.show', $election))
            ->assertOk()
            ->assertSee('Source Council')
            ->assertSee('President')
            ->assertSee('Source Candidate');

        $this->actingAs($super)
            ->post(route('admin.elections.duplicate', $election))
            ->assertRedirect();

        $copy = Election::query()->where('title', 'Source Council (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertSame(ElectionStatus::Draft, $copy->status);
        $this->assertSame($super->id, $copy->created_by);
        $this->assertSame(1, $copy->categories()->count());
        $this->assertSame(1, $copy->candidates()->count());
        $this->assertSame(0, $copy->votes()->count());
        $this->assertSame(1, $election->fresh()->votes()->count());

        $this->actingAs($super)
            ->from(route('admin.elections.index'))
            ->post(route('admin.elections.archive', $election))
            ->assertRedirect(route('admin.elections.index'))
            ->assertSessionHas('success', 'Election archived.');

        $this->assertSame(ElectionStatus::Archived, $election->fresh()->status);
    }

    public function test_regular_admin_without_modify_permission_cannot_duplicate_or_archive(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create(['title' => 'Assigned Council', 'created_by' => $admin->id]);
        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.elections.show', $election))
            ->assertOk()
            ->assertSee('Assigned Council');

        $this->actingAs($admin)
            ->post(route('admin.elections.duplicate', $election))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.elections.archive', $election))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.elections.open-voting', $election))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.elections.close-voting', $election))
            ->assertForbidden();
    }

    public function test_admin_can_open_and_close_voting_from_the_election_page(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->draft()->create([
            'title' => 'Scheduled Council',
            'voting_starts_at' => now()->addDay(),
            'voting_ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($super)
            ->get(route('admin.elections.show', $election))
            ->assertOk()
            ->assertSee('Open Voting')
            ->assertDontSee('>Close Voting<', false);

        $this->actingAs($super)
            ->from(route('admin.elections.show', $election))
            ->post(route('admin.elections.open-voting', $election))
            ->assertRedirect(route('admin.elections.show', $election))
            ->assertSessionHas('success', 'Voting is open.');

        $opened = $election->fresh();
        $this->assertSame(ElectionStatus::Active, $opened->status);
        $this->assertTrue($opened->isAcceptingVotes());

        $this->actingAs($super)
            ->get(route('admin.elections.show', $opened))
            ->assertOk()
            ->assertSee('Close Voting')
            ->assertDontSee('>Open Voting<', false);

        $this->actingAs($super)
            ->from(route('admin.elections.show', $opened))
            ->post(route('admin.elections.close-voting', $opened))
            ->assertRedirect(route('admin.elections.show', $opened))
            ->assertSessionHas('success', 'Voting is closed.');

        $closed = $election->fresh();
        $this->assertSame(ElectionStatus::Closed, $closed->status);
        $this->assertFalse($closed->isAcceptingVotes());

        $this->actingAs($super)
            ->from(route('admin.elections.show', $closed))
            ->post(route('admin.elections.open-voting', $closed))
            ->assertRedirect(route('admin.elections.show', $closed))
            ->assertSessionHas('success', 'Voting is open.');

        $this->assertSame(ElectionStatus::Active, $election->fresh()->status);
        $this->assertTrue($election->fresh()->isAcceptingVotes());
    }
}
