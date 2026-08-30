<?php

namespace Tests\Feature\Admin;

use App\Enums\PasskeyStatus;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPasskeyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_dashboard_defaults_to_active_passkeys_and_hides_revoked(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Active Device Student',
            'account_id' => '2026-4401',
        ]);
        $this->attachPasskey($student, 'Laptop', PasskeyStatus::Active);
        $this->attachPasskey($student, 'Old Phone', PasskeyStatus::Revoked);

        $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Active Device Student')
            ->assertSee('2026-4401')
            ->assertSee('Laptop')
            ->assertDontSee('Old Phone')
            ->assertSee('Mark lost')
            ->assertSee('Revoke disables this device')
            ->assertDontSee('Credential ID', false);
    }

    public function test_dashboard_can_search_and_show_revoked_passkeys(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Searchable Student',
            'account_id' => '2026-4402',
        ]);
        $other = User::factory()->create([
            'name' => 'Other Student',
            'account_id' => '2026-4499',
        ]);
        $this->attachPasskey($student, 'Revoked Tablet', PasskeyStatus::Revoked);
        $this->attachPasskey($other, 'Other Laptop', PasskeyStatus::Active);

        $this->actingAs($admin)
            ->get(route('super-admin.dashboard', [
                'passkey_status' => 'revoked',
                'passkey_q' => '2026-4402',
            ]))
            ->assertOk()
            ->assertSee('Searchable Student')
            ->assertSee('Revoked Tablet')
            ->assertDontSee('Other Laptop');
    }

    public function test_super_admin_can_revoke_a_passkey_and_sees_follow_up_actions(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Locked Out Student',
            'account_id' => '2026-4410',
            'phone' => '09171234567',
        ]);
        $passkey = $this->attachPasskey($student, 'Stolen Laptop');

        $this->actingAs($admin)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.passkeys.action', $passkey), ['action' => 'revoke'])
            ->assertRedirect(route('super-admin.dashboard'))
            ->assertSessionHas('success')
            ->assertSessionHas('disabled_passkey.account_id', '2026-4410')
            ->assertSessionHas('disabled_passkey.devices_url');

        $this->assertSame(PasskeyStatus::Revoked, $passkey->fresh()->status);
        $this->assertNotNull($passkey->fresh()->revoked_at);
        $this->assertFalse($passkey->fresh()->isUsable());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Revoked passkey for 2026-4410',
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('This device can no longer sign in.')
            ->assertSee('Reset Passkey')
            ->assertDontSee('Send link by SMS')
            ->assertDontSee('Stolen Laptop');
    }

    public function test_super_admin_can_mark_a_passkey_lost(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create(['account_id' => 'FACULTY-441']);
        $passkey = $this->attachPasskey($faculty, 'Missing Phone');

        $this->actingAs($admin)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.passkeys.action', $passkey), ['action' => 'lost'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(PasskeyStatus::Lost, $passkey->fresh()->status);
        $this->assertNotNull($passkey->fresh()->marked_lost_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Marked lost passkey for FACULTY-441',
        ]);
    }

    public function test_already_disabled_passkey_cannot_be_revoked_again(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $passkey = $this->attachPasskey($student, 'Dead Key', PasskeyStatus::Revoked);

        $this->actingAs($admin)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.passkeys.action', $passkey), ['action' => 'revoke'])
            ->assertRedirect()
            ->assertSessionHas('error', 'This passkey is already disabled.');
    }

    public function test_super_admin_cannot_disable_their_only_remaining_passkey(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $passkey = $this->attachPasskey($admin, 'Only Device');

        $this->actingAs($admin)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.passkeys.action', $passkey), ['action' => 'revoke'])
            ->assertRedirect()
            ->assertSessionHas('error', 'You cannot disable your only remaining passkey. Register another device in Settings first.');

        $this->assertTrue($passkey->fresh()->isUsable());
    }

    public function test_super_admin_can_revoke_own_passkey_when_another_remains(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $keep = $this->attachPasskey($admin, 'Backup Laptop');
        $drop = $this->attachPasskey($admin, 'Old Phone');

        $this->actingAs($admin)
            ->from(route('super-admin.dashboard'))
            ->post(route('super-admin.passkeys.action', $drop), ['action' => 'revoke'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($keep->fresh()->isUsable());
        $this->assertFalse($drop->fresh()->isUsable());
    }

    public function test_current_session_passkey_is_labelled_this_device(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $passkey = $this->attachPasskey($admin, 'Signed In Laptop');
        $this->attachPasskey($admin, 'Spare Phone');

        $this->actingAs($admin)
            ->withSession(['authenticated_passkey_id' => $passkey->id])
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('This device')
            ->assertSee('Signed In Laptop');
    }

    public function test_recovery_queue_link_shows_pending_count(): void
    {
        $admin = User::factory()->superAdmin()->create();
        PasskeyRecoveryRequest::query()->create([
            'account_id' => '2026-0001',
            'email' => 'lost@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $html = $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('data-recovery-queue-link', false)
            ->assertSee('data-recovery-header', false)
            ->assertSee('Passkey recovery queue, 1 pending', false)
            ->assertSee('bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-bold text-amber-100">1</span>', false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '/data-recovery-header-badge[^>]*\bhidden\b/',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/data-recovery-header-badge[^>]*>1</',
            $html,
        );
    }

    public function test_header_recovery_badge_is_hidden_when_queue_is_empty(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $html = $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('data-recovery-header', false)
            ->assertSee('data-recovery-header-badge', false)
            ->assertDontSee('Passkey recovery queue, ', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/data-recovery-header-badge[^>]*\bhidden\b/',
            $html,
        );
    }

    public function test_regular_admin_cannot_revoke_passkeys(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $passkey = $this->attachPasskey($student);

        $this->actingAs($admin)
            ->post(route('super-admin.passkeys.action', $passkey), ['action' => 'revoke'])
            ->assertForbidden();

        $this->assertTrue($passkey->fresh()->isUsable());
    }

    public function test_revoked_rows_do_not_offer_actions_when_viewing_all(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create(['name' => 'History Student']);
        $this->attachPasskey($student, 'Retired Key', PasskeyStatus::Revoked);

        $html = $this->actingAs($admin)
            ->get(route('super-admin.dashboard', ['passkey_status' => 'all']))
            ->assertOk()
            ->assertSee('History Student')
            ->assertSee('Disabled')
            ->getContent();

        $this->assertSame(0, substr_count($html, 'value="revoke"'));
    }

    protected function attachPasskey(User $user, string $name = 'Primary Device', PasskeyStatus $status = PasskeyStatus::Active): Passkey
    {
        $passkey = new Passkey([
            'name' => $name,
            'device_name' => $name,
            'credential_id' => 'cred-'.$user->id.'-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
            'status' => $status,
            'revoked_at' => $status === PasskeyStatus::Revoked ? now() : null,
            'marked_lost_at' => $status === PasskeyStatus::Lost ? now() : null,
        ]);
        $passkey->user_id = $user->id;
        $passkey->save();

        return $passkey;
    }
}
