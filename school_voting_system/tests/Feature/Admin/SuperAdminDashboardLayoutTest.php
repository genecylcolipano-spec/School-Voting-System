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
            ->assertSee('2xl:grid-cols-9', false)
            ->assertSee('xl:grid-cols-5', false)
            ->assertSee('data-permission-matrix-cards', false)
            ->assertSee('data-permission-matrix-table', false)
            ->assertSee('data-audit-cards', false)
            ->assertSee('data-passkey-cards', false)
            ->assertSee('data-portal-account-cards', false)
            ->assertSee('super-admin-search-mobile', false)
            ->assertSee('Responsive Layout Election')
            ->getContent();

        $this->assertSame(2, substr_count($html, 'data-super-admin-search-results'));
        $this->assertStringContainsString('id="super-admin-search"', $html);
        $this->assertStringContainsString('id="super-admin-search-mobile"', $html);
        $this->assertStringContainsString('>Actions<', $html);
        $this->assertStringContainsString('sm:flex-row sm:items-start sm:justify-between', $html);
        $this->assertStringContainsString('lg:hidden', $html);
        $this->assertStringContainsString('overflow-x-auto lg:block', $html);
        $this->assertStringContainsString('overflow-x-auto xl:block', $html);
    }

    public function test_regular_admin_does_not_see_super_admin_header_search(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('data-super-admin-search', false);
    }
}
