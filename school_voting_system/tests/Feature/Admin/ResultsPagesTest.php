<?php

namespace Tests\Feature\Admin;

use App\Models\Election;
use App\Models\PortalNotification;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_results_pages_use_unread_notification_count(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->closed()->create(['title' => 'Closed Council']);

        PortalNotification::query()->create([
            'title' => 'Results ping',
            'message' => 'Unread for results',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('admin.results.elections'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Official results for every student election.')
            ->assertSee('Closed Council')
            ->assertDontSee('in your scope');

        $this->actingAs($super)
            ->get(route('admin.results.competitions'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Official results for every talent competition.');

        $html = $this->actingAs($super)
            ->withSession(['success' => 'Unique integrity flash'])
            ->get(route('admin.results.election.show', $election))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Export PDF')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Unique integrity flash'));
    }

    public function test_super_admin_can_export_a_closed_election_without_an_assignment(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->closed()->create(['title' => 'Exportable Council']);

        $this->assertTrue(app(AdminScopeService::class)->canExportPreliminaryResults($super));

        $this->actingAs($super)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_regular_admin_without_assignment_cannot_export_results(): void
    {
        $admin = User::factory()->admin()->create();
        Election::factory()->closed()->create();

        $this->assertFalse(app(AdminScopeService::class)->canExportPreliminaryResults($admin));
    }
}
