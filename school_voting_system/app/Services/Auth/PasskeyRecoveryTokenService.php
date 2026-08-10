<?php

namespace App\Services\Auth;

use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PasskeyRecoveryTokenService
{
    public const SESSION_RECOVERY_REQUEST_ID = 'passkey.recovery_request_id';

    public function expirationMinutes(): int
    {
        return max(5, (int) config('enrollment.reset_link_expiration_minutes', 30));
    }

    public function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function invalidateActiveForUser(int $userId): void
    {
        PasskeyRecoveryRequest::query()
            ->where('user_id', $userId)
            ->whereNotNull('token_hash')
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => now()]);
    }

    /**
     * Issue a hashed, single-use recovery token and email the setup link.
     *
     * @return array{
     *     recovery: PasskeyRecoveryRequest,
     *     plain: string,
     *     email_sent: bool,
     *     email_error: string|null,
     *     enroll_url: string,
     *     expires_in_minutes: int
     * }
     */
    public function issueAndEmail(
        User $user,
        string $requestedEmail,
        ?string $ip = null,
        ?string $userAgent = null,
    ): array {
        $this->invalidateActiveForUser($user->id);

        $plain = Str::random(64);
        $minutes = $this->expirationMinutes();

        $recovery = PasskeyRecoveryRequest::query()->create([
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'email' => strtolower(trim($requestedEmail)),
            'token_hash' => $this->hash($plain),
            'expires_at' => now()->addMinutes($minutes),
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
            'requested_ip' => $ip,
            'requested_user_agent' => $userAgent,
        ]);

        $enrollUrl = route('login.recovery.continue', ['token' => $plain]);
        $emailResult = $this->sendEmail($user, $enrollUrl, $minutes, $recovery->id, $requestedEmail);

        if ($emailResult['sent']) {
            $recovery->forceFill([
                'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
                'resolved_at' => now(),
            ])->save();

            Log::info('Passkey recovery email dispatched.', [
                'recovery_request_id' => $recovery->id,
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'expires_in_minutes' => $minutes,
                'ip' => $ip,
            ]);
        } else {
            Log::warning('Passkey recovery email failed.', [
                'recovery_request_id' => $recovery->id,
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'ip' => $ip,
            ]);
        }

        return [
            'recovery' => $recovery,
            'plain' => $plain,
            'email_sent' => $emailResult['sent'],
            'email_error' => $emailResult['error'],
            'enroll_url' => $enrollUrl,
            'expires_in_minutes' => $minutes,
        ];
    }

    public function findByPlainToken(string $plain): ?PasskeyRecoveryRequest
    {
        if ($plain === '') {
            return null;
        }

        return PasskeyRecoveryRequest::query()
            ->where('token_hash', $this->hash($plain))
            ->first();
    }

    public function findUsableByPlainToken(string $plain): ?PasskeyRecoveryRequest
    {
        $recovery = $this->findByPlainToken($plain);

        if (! $recovery || ! $recovery->isTokenUsable()) {
            return null;
        }

        return $recovery;
    }

    public function markUsed(PasskeyRecoveryRequest $recovery): void
    {
        if ($recovery->used_at !== null) {
            return;
        }

        $recovery->forceFill([
            'used_at' => now(),
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
            'resolved_at' => $recovery->resolved_at ?? now(),
        ])->save();

        Log::info('Passkey recovery token consumed.', [
            'recovery_request_id' => $recovery->id,
            'user_id' => $recovery->user_id,
            'account_id' => $recovery->account_id,
        ]);
    }

    /**
     * @return array{sent: bool, error: string|null}
     */
    protected function sendEmail(
        User $user,
        string $enrollUrl,
        int $expiresInMinutes,
        int $recoveryRequestId,
        string $recipient,
    ): array {
        if (! filled($recipient)) {
            return ['sent' => false, 'error' => 'No email address on file.'];
        }

        try {
            Mail::to($recipient)->send(new PasskeyResetEnrollmentLinkMail(
                userName: $user->name,
                enrollmentUrl: $enrollUrl,
                expiresInMinutes: $expiresInMinutes,
                recoveryRequestId: $recoveryRequestId,
            ));

            return ['sent' => true, 'error' => null];
        } catch (Throwable $exception) {
            report($exception);

            return ['sent' => false, 'error' => 'Email delivery failed.'];
        }
    }
}
