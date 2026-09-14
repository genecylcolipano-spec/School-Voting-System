<?php

namespace Tests\Feature\Admin;

use App\Enums\AnnouncementAudience;
use App\Enums\AuditActionType;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\SchoolBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_super_admin_can_save_system_settings(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->put(route('super-admin.system.settings.update'), [
                'system_name' => 'Campus Ballot',
                'school_name' => 'Test College',
                'enable_student_registration' => '1',
                'enable_elections' => '1',
                'enable_talent_voting' => '0',
                'enable_fundraising' => '1',
                'announcement_default_visibility' => 'faculty',
                'announcement_default_expiration_days' => 21,
                'session_timeout_minutes' => 45,
                'two_factor_recovery_enabled' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) SystemSetting::getValue('enable_elections'));
        $this->assertFalse((bool) SystemSetting::getValue('enable_talent_voting'));
        $this->assertSame('faculty', SystemSetting::getValue('announcement_default_visibility'));
        $this->assertSame(21, (int) SystemSetting::getValue('announcement_default_expiration_days'));
        $this->assertSame(45, (int) SystemSetting::getValue('session_timeout_minutes'));
    }

    public function test_super_admin_can_upload_and_remove_school_logo(): void
    {
        Storage::fake('public');
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->put(route('super-admin.system.settings.update'), [
                'system_name' => 'Campus Ballot',
                'school_name' => 'Test College',
                'school_logo' => $this->tinyPngUpload(),
                'enable_student_registration' => '1',
                'enable_elections' => '1',
                'enable_talent_voting' => '0',
                'enable_fundraising' => '1',
                'announcement_default_visibility' => 'all',
                'announcement_default_expiration_days' => 14,
                'session_timeout_minutes' => 30,
                'two_factor_recovery_enabled' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $path = (string) SystemSetting::getValue('school_logo_path');
        $this->assertNotSame('', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull(SchoolBranding::logoUrl(withFallback: false));

        $this->actingAs($super)
            ->put(route('super-admin.system.settings.update'), [
                'system_name' => 'Campus Ballot',
                'school_name' => 'Test College',
                'remove_logo' => '1',
                'enable_student_registration' => '1',
                'enable_elections' => '1',
                'enable_talent_voting' => '0',
                'enable_fundraising' => '1',
                'announcement_default_visibility' => 'all',
                'announcement_default_expiration_days' => 14,
                'session_timeout_minutes' => 30,
                'two_factor_recovery_enabled' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('', (string) SystemSetting::getValue('school_logo_path'));
        Storage::disk('public')->assertMissing($path);
        $this->assertNull(SchoolBranding::logoUrl(withFallback: false));
    }

    public function test_school_logo_rejects_unsupported_files(): void
    {
        Storage::fake('public');
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->put(route('super-admin.system.settings.update'), [
                'system_name' => 'Campus Ballot',
                'school_logo' => UploadedFile::fake()->create('logo.svg', 20, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('school_logo');

        $this->assertSame('', (string) SystemSetting::getValue('school_logo_path', ''));
    }

    public function test_settings_page_shows_logo_preview_controls(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('super-admin.system.settings.edit'))
            ->assertOk()
            ->assertSee('School Logo', false)
            ->assertSee('schoolLogoPreview', false)
            ->assertSee('Sidebar', false)
            ->assertSee('transparent PNG', false)
            ->assertDontSee('php artisan', false)
            ->assertDontSee('public/storage', false);
    }

    public function test_disabled_election_module_blocks_students_and_regular_admins(): void
    {
        SystemSetting::setValue('enable_elections', false, 'boolean');

        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($student)
            ->get(route('student.voting.index'))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.elections.index'))
            ->assertForbidden();

        $this->actingAs($super)
            ->get(route('admin.elections.index'))
            ->assertOk();
    }

    public function test_disabled_fundraising_module_blocks_students_and_faculty(): void
    {
        SystemSetting::setValue('enable_fundraising', false, 'boolean');

        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($student)
            ->get(route('student.fundraising.index'))
            ->assertNotFound();

        $this->actingAs($faculty)
            ->get(route('faculty.fundraising.index'))
            ->assertNotFound();

        $this->actingAs($super)
            ->get(route('admin.fundraisers.index'))
            ->assertOk();
    }

    public function test_announcement_create_form_uses_system_defaults(): void
    {
        SystemSetting::setValue('announcement_default_visibility', 'all', 'string');
        SystemSetting::setValue('announcement_default_expiration_days', 14, 'integer');

        $super = User::factory()->superAdmin()->create();

        $html = $this->actingAs($super)
            ->get(route('admin.announcements.create'))
            ->assertOk()
            ->assertSee('value="'.AnnouncementAudience::AllUsers->value.'"', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/name="send_email"[^>]*checked|checked[^>]*name="send_email"/',
            $html,
        );
    }

    public function test_recovery_page_is_blocked_when_disabled(): void
    {
        SystemSetting::setValue('two_factor_recovery_enabled', false, 'boolean');

        $this->get(route('login.recovery'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->post(route('login.recovery.request'), [
            'account_id' => '600045',
            'email' => 'student@example.com',
        ])->assertRedirect();
    }

    public function test_audit_export_honors_module_filter(): void
    {
        $super = User::factory()->superAdmin()->create();

        AuditLog::query()->create([
            'user_id' => $super->id,
            'admin_name' => $super->name,
            'admin_role' => 'super_admin',
            'action' => 'Updated System Settings',
            'action_type' => AuditActionType::System,
            'ip_address' => '127.0.0.1',
            'status' => 'success',
        ]);
        AuditLog::query()->create([
            'user_id' => $super->id,
            'admin_name' => $super->name,
            'admin_role' => 'super_admin',
            'action' => 'Opened election',
            'action_type' => AuditActionType::Election,
            'ip_address' => '127.0.0.1',
            'status' => 'success',
        ]);

        $csv = $this->actingAs($super)
            ->get(route('super-admin.audit.export', ['module' => AuditActionType::System->value]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Updated System Settings', $csv);
        $this->assertStringNotContainsString('Opened election', $csv);
    }

    public function test_maintenance_can_be_enabled_and_disabled(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();

        $this->actingAs($super)
            ->post(route('super-admin.system.maintenance.enable'), [
                'message' => 'Campus portal is offline for upgrades.',
                'allow_super_admin' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertStatus(503)
            ->assertSee('Campus portal is offline for upgrades.');

        $this->actingAs($super)
            ->get(route('super-admin.system.settings.edit'))
            ->assertOk();

        $this->actingAs($super)
            ->post(route('super-admin.system.maintenance.disable'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk();
    }

    public function test_backup_page_is_named_backups_and_create_works(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('super-admin.system.backups.index'))
            ->assertOk()
            ->assertSee('Backups')
            ->assertDontSee('Backup & Restore')
            ->assertSee('Restore from this screen is not available yet.');

        $this->actingAs($super)
            ->post(route('super-admin.system.backups.store'), [
                'type' => 'full_system',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('system_backups', 1);
    }

    private function tinyPngUpload(string $name = 'seal.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name;
        file_put_contents($path, $png);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
