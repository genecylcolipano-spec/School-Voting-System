<?php

namespace Tests\Feature\Auth;

use App\Enums\PasskeyStatus;
use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use App\Services\Auth\PasskeyRecoveryTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasskeyRecoveryResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_account_and_matching_email_dispatches_reset_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'account_id' => '2026-0007',
            'email' => 'genzkycolipz@gmail.com',
            'name' => 'Test Student',
        ]);

        $response = $this->postJson(route('login.recovery.request'), [
            'account_id' => '2026-0007',
            'email' => 'genzkycolipz@gmail.com',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'If your account details are valid, a passkey reset link has been sent to the registered email address.',
            ]);

        Mail::assertSent(PasskeyResetEnrollmentLinkMail::class, function (PasskeyResetEnrollmentLinkMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->selfService === true
                && str_contains($mail->enrollmentUrl, '/login/recovery/continue/');
        });

        $this->assertDatabaseHas('passkey_recovery_requests', [
            'user_id' => $user->id,
            'account_id' => '2026-0007',
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
        ]);

        $recovery = PasskeyRecoveryRequest::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($recovery?->token_hash);
        $this->assertNotNull($recovery?->expires_at);
        $this->assertNull($recovery?->used_at);
    }

    public function test_account_id_with_wrong_email_does_not_send_usable_reset_mail(): void
    {
        Mail::fake();

        User::factory()->create([
            'account_id' => '2026-0007',
            'email' => 'owner@example.com',
        ]);

        $other = User::factory()->create([
            'account_id' => '2026-0008',
            'email' => 'other@example.com',
        ]);

        $response = $this->postJson(route('login.recovery.request'), [
            'account_id' => '2026-0007',
            'email' => $other->email,
        ]);

        $response->assertOk();
        Mail::assertNothingSent();

        $this->assertSame(
            0,
            PasskeyRecoveryRequest::query()->whereNotNull('token_hash')->count()
        );
    }

    public function test_nonexistent_account_returns_generic_response(): void
    {
        Mail::fake();

        $response = $this->postJson(route('login.recovery.request'), [
            'account_id' => 'NO-SUCH-ID',
            'email' => 'nobody@example.com',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'If your account details are valid, a passkey reset link has been sent to the registered email address.',
            ]);

        Mail::assertNothingSent();
    }

    public function test_invalid_email_format_returns_validation_error(): void
    {
        Mail::fake();

        $response = $this->postJson(route('login.recovery.request'), [
            'account_id' => '2026-0007',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_expired_token_cannot_continue(): void
    {
        $user = User::factory()->create();
        $service = app(PasskeyRecoveryTokenService::class);
        $plain = 'expiredtokenabcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnop';

        $recovery = PasskeyRecoveryRequest::query()->create([
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'email' => $user->email,
            'token_hash' => $service->hash($plain),
            'expires_at' => now()->subMinute(),
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
            'resolved_at' => now()->subHour(),
        ]);

        $this->get(route('login.recovery.continue', ['token' => $plain]))
            ->assertRedirect(route('login.recovery'))
            ->assertSessionHas('status', 'The reset link has expired.');

        $this->assertNull($recovery->fresh()->used_at);
    }

    public function test_used_token_cannot_continue(): void
    {
        $user = User::factory()->create();
        $service = app(PasskeyRecoveryTokenService::class);
        $plain = 'usedtokenabcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrs';

        PasskeyRecoveryRequest::query()->create([
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'email' => $user->email,
            'token_hash' => $service->hash($plain),
            'expires_at' => now()->addMinutes(30),
            'used_at' => now(),
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $this->get(route('login.recovery.continue', ['token' => $plain]))
            ->assertRedirect(route('login.recovery'))
            ->assertSessionHas('status', 'The link is no longer valid.');
    }

    public function test_valid_token_opens_enrollment_and_mark_used_invalidates_reuse(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'account_id' => 'FAC-77',
            'email' => 'faculty@example.com',
        ]);

        $this->postJson(route('login.recovery.request'), [
            'account_id' => 'FAC-77',
            'email' => 'faculty@example.com',
        ])->assertOk();

        /** @var PasskeyResetEnrollmentLinkMail $mail */
        $mail = Mail::sent(PasskeyResetEnrollmentLinkMail::class)->first();
        $this->assertNotNull($mail);

        preg_match('#/login/recovery/continue/([A-Za-z0-9]+)#', $mail->enrollmentUrl, $matches);
        $plain = $matches[1] ?? null;
        $this->assertNotEmpty($plain);

        $this->get(route('login.recovery.continue', ['token' => $plain]))
            ->assertOk()
            ->assertViewIs('auth.enroll-passkey')
            ->assertSessionHas('passkey.bootstrap_user_id', $user->id)
            ->assertSessionHas(PasskeyRecoveryTokenService::SESSION_RECOVERY_REQUEST_ID);

        $recovery = PasskeyRecoveryRequest::query()->where('user_id', $user->id)->whereNotNull('token_hash')->first();
        app(PasskeyRecoveryTokenService::class)->markUsed($recovery);

        $this->get(route('login.recovery.continue', ['token' => $plain]))
            ->assertRedirect(route('login.recovery'))
            ->assertSessionHas('status', 'The link is no longer valid.');
    }

    public function test_new_reset_request_invalidates_previous_token(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'account_id' => 'ADM-01',
            'email' => 'admin@example.com',
        ]);

        RateLimiter::clear('passkey-recovery-cooldown:'.$user->id);
        RateLimiter::clear('passkey-recovery-account:'.sha1(strtolower($user->account_id).'|'.strtolower($user->email)));

        $this->postJson(route('login.recovery.request'), [
            'account_id' => 'ADM-01',
            'email' => 'admin@example.com',
        ])->assertOk();

        $firstMail = Mail::sent(PasskeyResetEnrollmentLinkMail::class)->first();
        preg_match('#/login/recovery/continue/([A-Za-z0-9]+)#', $firstMail->enrollmentUrl, $firstMatches);
        $firstPlain = $firstMatches[1];

        RateLimiter::clear('passkey-recovery-cooldown:'.$user->id);
        RateLimiter::clear('passkey-recovery-account:'.sha1(strtolower($user->account_id).'|'.strtolower($user->email)));

        $this->postJson(route('login.recovery.request'), [
            'account_id' => 'ADM-01',
            'email' => 'admin@example.com',
        ])->assertOk();

        $this->assertSame(2, Mail::sent(PasskeyResetEnrollmentLinkMail::class)->count());

        $this->get(route('login.recovery.continue', ['token' => $firstPlain]))
            ->assertRedirect(route('login.recovery'))
            ->assertSessionHas('status', 'The link is no longer valid.');

        $secondMail = Mail::sent(PasskeyResetEnrollmentLinkMail::class)->last();
        preg_match('#/login/recovery/continue/([A-Za-z0-9]+)#', $secondMail->enrollmentUrl, $secondMatches);

        $this->get(route('login.recovery.continue', ['token' => $secondMatches[1]]))
            ->assertOk()
            ->assertViewIs('auth.enroll-passkey');
    }

    public function test_rate_limiting_blocks_excessive_account_requests(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'account_id' => 'RATE-1',
            'email' => 'rate@example.com',
        ]);

        $key = 'passkey-recovery-account:'.sha1(strtolower($user->account_id).'|'.strtolower($user->email));
        RateLimiter::clear($key);
        RateLimiter::clear('passkey-recovery-cooldown:'.$user->id);

        for ($i = 0; $i < 3; $i++) {
            RateLimiter::clear('passkey-recovery-cooldown:'.$user->id);
            $this->postJson(route('login.recovery.request'), [
                'account_id' => 'RATE-1',
                'email' => 'rate@example.com',
            ])->assertOk();
        }

        $response = $this->postJson(route('login.recovery.request'), [
            'account_id' => 'RATE-1',
            'email' => 'rate@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_successful_reset_revokes_previous_passkeys(): void
    {
        $user = User::factory()->create([
            'account_id' => 'REV-01',
            'email' => 'revoke@example.com',
        ]);

        $old = new Passkey([
            'name' => 'Old Device',
            'device_name' => 'Old Device',
            'credential_id' => 'old-cred-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
            'status' => PasskeyStatus::Active,
        ]);
        $old->user_id = $user->id;
        $old->save();

        $new = new Passkey([
            'name' => 'New Device',
            'device_name' => 'New Device',
            'credential_id' => 'new-cred-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
            'status' => PasskeyStatus::Active,
        ]);
        $new->user_id = $user->id;
        $new->save();

        $revoked = Passkey::revokeOthersForUser($user, (int) $new->id, (int) $user->id);

        $this->assertSame(1, $revoked);
        $this->assertSame(PasskeyStatus::Revoked, $old->fresh()->status);
        $this->assertNotNull($old->fresh()->revoked_at);
        $this->assertFalse($old->fresh()->isUsable());
        $this->assertTrue($new->fresh()->isUsable());
    }
}
