<?php

namespace Tests\Feature\Admin;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_super_admin_dashboard_renders_responsive_layout_hooks(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->create([
            'title' => 'Responsive Layout Election',
            'created_by' => $admin->id,
        ]);

        $html = $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Chief Super Administrator')
            ->assertDontSee('2xl:grid-cols-9', false)
            ->assertSee('xl:grid-cols-5', false)
            ->assertSee('md:grid-cols-2 lg:grid-cols-3', false)
            ->assertSee('min-h-10 min-w-0', false)
            ->assertSee('max-sm:last:col-span-2', false)
            ->assertSee('tabular-nums', false)
            ->assertSee('flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between', false)
            ->assertSee('data-permission-matrix-cards', false)
            ->assertSee('data-permission-matrix-table', false)
            ->assertSee('data-audit-cards', false)
            ->assertSee('data-passkey-cards', false)
            ->assertSee('data-portal-account-cards', false)
            ->assertSee('super-admin-search-mobile', false)
            ->assertSee('data-super-admin-overview-charts', false)
            ->assertSee('Participation Growth (Events/Voting)')
            ->assertSee('Donation/Fundraising History')
            ->assertSee(route('admin.analytics.index'), false)
            ->assertSee('Responsive Layout Election')
            ->getContent();

        $this->assertSame(2, substr_count($html, 'data-super-admin-search-results'));
        $this->assertSame(0, substr_count($html, 'Passkey Recovery Requests'));
        $this->assertStringContainsString('data-pending-recovery-stat', $html);
        $this->assertStringContainsString('Open queue', $html);
        $this->assertStringContainsString('data-recovery-queue-link', $html);
        $this->assertStringContainsString('Mark lost', $html);
        $this->assertStringContainsString('Revoke disables this device', $html);
        $this->assertStringNotContainsString('Generate enrollment link', $html);
        $this->assertStringContainsString('id="super-admin-search"', $html);
        $this->assertStringContainsString('id="super-admin-search-mobile"', $html);
        $this->assertStringContainsString('>Actions<', $html);
        $this->assertStringContainsString('sm:flex-row sm:items-start sm:justify-between', $html);
        $this->assertStringContainsString('lg:hidden', $html);
        $this->assertStringContainsString('overflow-x-auto lg:block', $html);
        $this->assertStringContainsString('overflow-x-auto xl:block', $html);

        $sectionOrder = [
            'Chief Super Administrator',
            'Participation Growth (Events/Voting)',
            'Donation/Fundraising History',
            'Live election',
            'Election Lifecycle Controls',
            'Vote Integrity & Verification',
            'Portal Accounts & Bulk Actions',
            'Audit Log / Activity History',
            'Advanced Passkey Management',
            'Granular Role & Permission Matrix',
            'Application-wide administration',
            'Compliance & Official Reports',
        ];
        $last = -1;
        foreach ($sectionOrder as $heading) {
            $pos = strpos($html, $heading);
            $this->assertNotFalse($pos, $heading.' should be on the dashboard');
            $this->assertGreaterThan($last, $pos, $heading.' should follow the previous section');
            $last = $pos;
        }
    }

    public function test_regular_admin_does_not_see_super_admin_header_search(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('data-super-admin-search', false)
            ->assertDontSee('data-recovery-header', false);
    }
}
