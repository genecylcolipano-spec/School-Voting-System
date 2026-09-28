<?php

namespace Tests\Feature\Admin;

use App\Enums\StudentStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Permission;
use App\Models\PortalNotification;
use App\Models\StaffRole;
use App\Models\TalentEvent;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminScopeService;
use App\Support\SchoolBranding;
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

        $this->actingAs($super)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'print']))
            ->assertOk()
            ->assertSee('Exportable Council');
    }

    public function test_regular_admin_without_assignment_cannot_export_results(): void
    {
        $admin = User::factory()->admin()->create();
        Election::factory()->closed()->create();

        $this->assertFalse(app(AdminScopeService::class)->canExportPreliminaryResults($admin));
    }

    public function test_file_export_is_forbidden_while_election_voting_is_open(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['title' => 'Live Export Block']);

        $this->actingAs($super)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'pdf']))
            ->assertForbidden();
    }

    public function test_operations_admin_can_export_a_closed_election_they_created(): void
    {
        $admin = $this->makeOperationsAdmin();
        $election = Election::factory()->closed()->create([
            'title' => 'Ops Created Council',
            'created_by' => $admin->id,
        ]);

        $this->assertTrue(app(AdminScopeService::class)->canExportElectionResults($admin, $election));

        $this->actingAs($admin)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_assigned_operations_admin_can_export_a_closed_election(): void
    {
        $admin = $this->makeOperationsAdmin();
        $election = Election::factory()->closed()->create(['title' => 'Assigned Council']);
        app(AdminScopeService::class)->assignElectionToAdmin($admin, $election);

        $this->actingAs($admin)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'print']))
            ->assertOk()
            ->assertSee('Assigned Council');
    }

    public function test_operations_admin_cannot_export_another_admins_closed_election(): void
    {
        $admin = $this->makeOperationsAdmin();
        $election = Election::factory()->closed()->create([
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.results.election.export', ['election' => $election, 'format' => 'csv']))
            ->assertForbidden();
    }

    public function test_operations_admin_can_export_unpublished_talent_after_voting_ends(): void
    {
        $admin = $this->makeOperationsAdmin();
        $event = $this->makeCompetition([
            'title' => 'Closed Idol',
            'created_by' => $admin->id,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => null,
        ]);

        $this->assertTrue(app(AdminScopeService::class)->talentFileExportIsReady($event));

        $this->actingAs($admin)
            ->get(route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $excelBody = $this->actingAs($admin)
            ->get(route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'excel']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')
            ->streamedContent();

        $this->assertStringContainsString(SchoolBranding::schoolName(), $excelBody);
        $this->assertStringContainsString(SchoolBranding::systemName(), $excelBody);
        $this->assertStringContainsString(SchoolBranding::semester(), $excelBody);
        $this->assertStringContainsString(SchoolBranding::academicYear(), $excelBody);
        $this->assertStringContainsString('Official Talent Competition Results', $excelBody);
        $this->assertStringContainsString('Closed Idol', $excelBody);
    }

    public function test_operations_admin_sees_talent_competitions_created_by_super_admin(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = $this->makeOperationsAdmin();
        $event = $this->makeCompetition([
            'title' => 'Campus Idol',
            'created_by' => $super->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.results.competitions'))
            ->assertOk()
            ->assertSee('Campus Idol')
            ->assertSee('Official results for talent competitions you can manage')
            ->assertDontSee('No Talent Competition Results');

        $this->actingAs($admin)
            ->get(route('admin.results.talent.show', $event))
            ->assertOk()
            ->assertSee('Campus Idol');
    }

    public function test_operations_admin_can_export_super_created_talent_after_voting_ends(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = $this->makeOperationsAdmin();
        $event = $this->makeCompetition([
            'title' => 'Closed Idol',
            'created_by' => $super->id,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => null,
        ]);

        $this->assertTrue(app(AdminScopeService::class)->canExportTalentResults($admin, $event));
        $this->assertTrue(app(AdminScopeService::class)->talentFileExportIsReady($event));

        $this->actingAs($admin)
            ->get(route('admin.results.talent.show', $event))
            ->assertOk()
            ->assertSee('Review Results')
            ->assertSee('Export PDF')
            ->assertSee('Export Excel')
            ->assertSee('Export CSV')
            ->assertSee('Print Results');

        $this->actingAs($admin)
            ->get(route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_talent_results_show_file_exports_after_close_voting_without_publish(): void
    {
        $super = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition([
            'title' => 'ROSEMONT IDOL',
            'created_by' => $super->id,
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now()->subHours(2),
            'voting_ends_at' => now()->subMinute(),
            'results_published_at' => null,
        ]);

        $this->actingAs($super)
            ->get(route('admin.results.talent.show', $event))
            ->assertOk()
            ->assertSee('ROSEMONT IDOL')
            ->assertSee('Review Results')
            ->assertSee('Export PDF')
            ->assertSee('Export Excel')
            ->assertSee('Export CSV')
            ->assertSee('Print Results')
            ->assertDontSee('PDF, Excel, and printable reports are available after voting and judging have ended.');
    }

    public function test_talent_results_hide_file_exports_while_voting_is_open(): void
    {
        $super = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition([
            'title' => 'Live Idol',
            'created_by' => $super->id,
        ]);

        $this->actingAs($super)
            ->get(route('admin.results.talent.show', $event))
            ->assertOk()
            ->assertSee('Live Idol')
            ->assertDontSee('Export PDF')
            ->assertSee('PDF, Excel, and printable reports are available after voting and judging have ended.');
    }

    public function test_admin_without_talent_scope_cannot_export_closed_talent(): void
    {
        $event = $this->makeCompetition([
            'title' => 'Closed Idol',
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => null,
        ]);
        $admin = $this->makeOperationsAdmin(['export_reports']);

        $this->actingAs($admin)
            ->get(route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'csv']))
            ->assertForbidden();
    }

    public function test_regular_admin_without_talent_permission_does_not_see_another_admins_competition(): void
    {
        $this->makeCompetition(['title' => 'Hidden Idol']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.results.competitions'))
            ->assertOk()
            ->assertDontSee('Hidden Idol');
    }

    public function test_talent_file_export_is_forbidden_while_voting_is_open(): void
    {
        $admin = $this->makeOperationsAdmin();
        $event = $this->makeCompetition([
            'title' => 'Live Idol',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'excel']))
            ->assertForbidden();
    }

    public function test_results_dashboard_defers_integrity_and_section_turnout(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Dashboard Council',
            'integrity_hash' => 'stored-hash-preview',
        ]);
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
        ]);
        $candidate = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Dashboard Winner',
        ]);
        $voter = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '12',
            'section' => 'Ruby',
        ]);
        Vote::castBallot($voter, $candidate);

        $this->actingAs($super)
            ->get(route('admin.results.election.show', $election))
            ->assertOk()
            ->assertSee('Dashboard Council')
            ->assertSee('Dashboard Winner')
            ->assertSee('Not verified yet')
            ->assertSee('Load participation by grade / section')
            ->assertSee('stored-hash-preview')
            ->assertSee('PDF, Excel, and printable reports are available after voting has ended.')
            ->assertDontSee('Export PDF')
            ->assertDontSee('Ruby')
            ->assertDontSee('Mismatch');

        $this->actingAs($super)
            ->get(route('admin.results.election.show', [$election, 'breakdown' => 1]))
            ->assertOk()
            ->assertSee('Ruby')
            ->assertDontSee('Load participation by grade / section');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Export Showcase',
            'slug' => 'export-showcase-'.uniqid(),
            'event_date' => now()->subDay(),
            'venue' => 'Online',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHour(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function makeOperationsAdmin(array $permissionKeys = ['export_reports', 'create_talent_events', 'modify_elections']): User
    {
        $permissions = collect($permissionKeys)->map(
            function (string $key) {
                return Permission::query()->firstOrCreate(
                    ['key' => $key],
                    ['label' => $key, 'category' => 'reports'],
                );
            }
        );

        $role = StaffRole::query()->create([
            'name' => 'Operations Admin',
            'slug' => 'election_admin_export_'.uniqid(),
            'description' => 'Can export reports',
            'is_system' => true,
        ]);
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->admin()->create([
            'staff_role_id' => $role->id,
        ]);
    }
}
