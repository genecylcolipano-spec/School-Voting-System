<?php

namespace Tests\Feature\Admin;

use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasskeyRecoveryQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_unmatched_retries_keep_one_pending_row(): void
    {
        Mail::fake();

        User::factory()->faculty()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'faculty.onfile@example.com',
        ]);

        $payload = [
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
        ];

        $this->postJson(route('login.recovery.request'), $payload)->assertOk();
        $this->postJson(route('login.recovery.request'), $payload)->assertOk();

        $this->assertSame(1, PasskeyRecoveryRequest::query()->count());
        $this->assertDatabaseHas('passkey_recovery_requests', [
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
            'user_id' => null,
        ]);
        Mail::assertNothingSent();
    }

    public function test_queue_shows_account_found_when_email_does_not_match(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create([
            'account_id' => 'FACULTY-801',
            'name' => 'Prof Queue',
            'email' => 'faculty.onfile@example.com',
        ]);

        PasskeyRecoveryRequest::query()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $this->actingAs($super)
            ->get(route('admin.recovery.index'))
            ->assertOk()
            ->assertSee('Prof Queue')
            ->assertSee('Account found · email does not match', false)
            ->assertSee('On file: faculty.onfile@example.com')
            ->assertSee('Generate enrollment link')
            ->assertSee('Dismiss')
            ->assertSee(route('super-admin.faculty.show', $faculty), false);
    }

    public function test_existing_duplicate_pending_rows_are_collapsed_on_the_queue(): void
    {
        $super = User::factory()->superAdmin()->create();

        PasskeyRecoveryRequest::query()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);
        PasskeyRecoveryRequest::query()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);
        PasskeyRecoveryRequest::query()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $html = $this->actingAs($super)
            ->get(route('admin.recovery.index'))
            ->assertOk()
            ->getContent();

        preg_match_all('/data-recovery-row="(\d+)"/', $html, $matches);
        $this->assertCount(1, array_unique($matches[1]));
        $this->assertSame(2, PasskeyRecoveryRequest::query()->where('status', PasskeyRecoveryRequest::STATUS_DISMISSED)->count());
        $this->assertSame(1, PasskeyRecoveryRequest::query()->where('status', PasskeyRecoveryRequest::STATUS_PENDING)->count());
    }

    public function test_unknown_account_cannot_be_enrolled_and_can_be_dismissed(): void
    {
        Mail::fake();

        $super = User::factory()->superAdmin()->create();
        $recovery = PasskeyRecoveryRequest::query()->create([
            'account_id' => 'NO-SUCH-ID',
            'email' => 'nobody@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $this->actingAs($super)
            ->get(route('admin.recovery.index'))
            ->assertOk()
            ->assertSee('No such account')
            ->assertDontSee('Generate enrollment link');

        $this->actingAs($super)
            ->postJson(route('admin.recovery.enroll', $recovery))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'No portal account matches this Account ID.']);

        $this->actingAs($super)
            ->postJson(route('admin.recovery.dismiss', $recovery))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Recovery request dismissed.']);

        $this->assertDatabaseHas('passkey_recovery_requests', [
            'id' => $recovery->id,
            'status' => PasskeyRecoveryRequest::STATUS_DISMISSED,
            'resolved_by' => $super->id,
        ]);
        Mail::assertNothingSent();
    }

    public function test_admin_enroll_emails_the_address_on_file(): void
    {
        Mail::fake();

        $super = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'faculty.onfile@example.com',
        ]);
        $recovery = PasskeyRecoveryRequest::query()->create([
            'account_id' => 'FACULTY-801',
            'email' => 'attacker@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $this->actingAs($super)
            ->postJson(route('admin.recovery.enroll', $recovery))
            ->assertOk()
            ->assertJsonPath('email_sent', true)
            ->assertJsonPath('recipient', 'faculty.onfile@example.com');

        Mail::assertSent(PasskeyResetEnrollmentLinkMail::class, function (PasskeyResetEnrollmentLinkMail $mail) use ($faculty) {
            return $mail->hasTo($faculty->email)
                && $mail->selfService === false;
        });
        Mail::assertNotSent(PasskeyResetEnrollmentLinkMail::class, function (PasskeyResetEnrollmentLinkMail $mail) {
            return $mail->hasTo('attacker@example.com');
        });

        $this->assertDatabaseHas('passkey_recovery_requests', [
            'id' => $recovery->id,
            'user_id' => $faculty->id,
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
        ]);
        $this->assertNotNull($recovery->fresh()->last_sent_at);
    }

    public function test_regular_admin_cannot_dismiss_recovery_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $recovery = PasskeyRecoveryRequest::query()->create([
            'account_id' => '2026-0004',
            'email' => 'wrong@example.com',
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.recovery.dismiss', $recovery))
            ->assertForbidden();

        $this->assertDatabaseHas('passkey_recovery_requests', [
            'id' => $recovery->id,
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
        ]);
    }
}
