<?php

namespace Tests\Feature\Admin;

use App\Enums\PasskeyStatus;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminComplianceReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_dashboard_compliance_section_explains_snapshots_and_links(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->closed()->create(['title' => 'Filed Council']);

        $html = $this->actingAs($admin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->getContent();

        foreach ([
            'Compliance & Official Reports',
            'Download PDF snapshots for filing',
            'Election for Summary and Turnout',
            'Election Summary PDF',
            'Filed Council',
            'Full audit logs',
            'Export audit CSV',
        ] as $needle) {
            $this->assertTrue(str_contains($html, $needle), 'Dashboard should include: '.$needle);
        }
    }

    public function test_election_summary_index_escapes_titles_and_opens_inline(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->closed()->create([
            'title' => '<script>alert(1)</script> Council',
            'integrity_hash' => 'hash-abc',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'election_summary',
                'format' => 'html',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->assertStringContainsString('inline;', (string) $response->headers->get('Content-Disposition'));
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; Council', false);
        $response->assertSee('hash-abc');
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Generated report: election_summary',
        ]);
    }

    public function test_election_summary_and_turnout_use_the_selected_election(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $ignored = Election::factory()->closed()->create(['title' => 'Other Race']);
        $picked = Election::factory()->closed()->create([
            'title' => 'Selected Council',
            'public_results_published' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'voter_turnout',
                'election_id' => $picked->id,
                'format' => 'html',
            ]))
            ->assertOk()
            ->assertSee('Selected Council')
            ->assertSee('Students voted')
            ->assertDontSee('Other Race');

        $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'election_summary',
                'election_id' => $picked->id,
                'format' => 'html',
            ]))
            ->assertOk()
            ->assertSee('Selected Council')
            ->assertSee('Published to students')
            ->assertDontSee($ignored->title);
    }

    public function test_passkey_inventory_omits_credential_ids_and_uses_status_labels(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Inventory Student',
            'account_id' => '2026-7701',
        ]);
        $passkey = new Passkey([
            'name' => 'School Laptop',
            'device_name' => 'School Laptop',
            'credential_id' => 'secret-cred-should-not-leak',
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
            'status' => PasskeyStatus::Lost,
            'marked_lost_at' => now(),
        ]);
        $passkey->user_id = $student->id;
        $passkey->save();

        $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'passkey_inventory',
                'format' => 'html',
            ]))
            ->assertOk()
            ->assertSee('Inventory Student')
            ->assertSee('2026-7701')
            ->assertSee('School Laptop')
            ->assertSee('Marked Lost')
            ->assertDontSee('secret-cred-should-not-leak');
    }

    public function test_audit_trail_includes_ip_and_type(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AuditLog::query()->create([
            'user_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_role' => 'super_admin',
            'action' => 'Revoked passkey for 2026-4410',
            'action_type' => 'passkey',
            'ip_address' => '10.8.1.4',
            'status' => 'success',
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'audit_trail',
                'format' => 'html',
            ]))
            ->assertOk()
            ->assertSee('Revoked passkey for 2026-4410')
            ->assertSee('10.8.1.4')
            ->assertSee('passkey');
    }

    public function test_regular_admin_cannot_generate_compliance_reports(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'audit_trail',
                'format' => 'pdf',
            ]))
            ->assertForbidden();
    }

    public function test_election_summary_pdf_downloads_without_script_injection(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Election::factory()->closed()->create([
            'title' => '<script>alert(1)</script> Council',
            'integrity_hash' => 'hash-abc',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'election_summary',
                'format' => 'pdf',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $pdf);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Generated report: election_summary (pdf)',
        ]);
    }

    public function test_passkey_inventory_pdf_omits_credential_ids(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Inventory Student',
            'account_id' => '2026-7701',
        ]);
        $passkey = new Passkey([
            'name' => 'School Laptop',
            'device_name' => 'School Laptop',
            'credential_id' => 'secret-cred-should-not-leak',
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
            'status' => PasskeyStatus::Lost,
            'marked_lost_at' => now(),
        ]);
        $passkey->user_id = $student->id;
        $passkey->save();

        $response = $this->actingAs($admin)
            ->get(route('super-admin.reports.generate', [
                'report' => 'passkey_inventory',
                'format' => 'pdf',
            ]));

        $response->assertOk();
        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringNotContainsString('secret-cred-should-not-leak', $pdf);
    }
}
