<?php

namespace App\Services\Auth;

use App\Mail\RosterPasskeyEnrollmentMail;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\EnrollmentToken;
use App\Support\RosterMatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class EnrollmentTokenService
{
    public const SESSION_PLAIN_TOKEN = 'enrollment.plain_token';

    public const SESSION_TOKEN_ID = 'enrollment.token_id';

    /**
     * @return array{token: EnrollmentToken, plain: string, email_sent: bool, email_error: string|null, enroll_url: string}
     */
    public function issueForMatch(RosterMatch $match, string $email, string $firstName, string $lastName): array
    {
        $this->invalidateActiveForRoster($match->rosterType, (int) $match->record->getKey());

        $plain = Str::random(64);
        $hours = max(1, (int) config('enrollment.link_expiration_hours', 24));

        $payload = $match->toPendingPayload($email, $firstName, $lastName);

        $token = EnrollmentToken::query()->create([
            'token_hash' => $this->hash($plain),
            'roster_type' => $match->rosterType,
            'roster_id' => $match->record->getKey(),
            'account_id' => $match->accountId,
            'email' => strtolower(trim($email)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $match->role->value,
            'payload' => $payload,
            'expires_at' => now()->addHours($hours),
        ]);

        if (method_exists($match->record, 'markEnrollmentPending')) {
            $match->record->markEnrollmentPending();
        }

        $enrollUrl = route('register.enroll', ['token' => $plain]);
        $emailResult = $this->sendEnrollmentEmail($token, $enrollUrl, $hours);

        return [
            'token' => $token,
            'plain' => $plain,
            'email_sent' => $emailResult['sent'],
            'email_error' => $emailResult['error'],
            'enroll_url' => $enrollUrl,
        ];
    }

    public function findUsableByPlainToken(string $plain): ?EnrollmentToken
    {
        $token = EnrollmentToken::query()
            ->where('token_hash', $this->hash($plain))
            ->first();

        if (! $token || ! $token->isUsable()) {
            return null;
        }

        return $token;
    }

    public function findByPlainToken(string $plain): ?EnrollmentToken
    {
        return EnrollmentToken::query()
            ->where('token_hash', $this->hash($plain))
            ->first();
    }

    public function invalidateActiveForRoster(string $rosterType, int $rosterId): void
    {
        EnrollmentToken::query()
            ->where('roster_type', $rosterType)
            ->where('roster_id', $rosterId)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => now()]);
    }

    /**
     * @return array<string, mixed>
     */
    public function pendingPayloadFromToken(EnrollmentToken $token): array
    {
        $payload = is_array($token->payload) ? $token->payload : [];

        return array_merge($payload, [
            'roster_type' => $token->roster_type,
            'roster_id' => $token->roster_id,
            'role' => $token->role?->value ?? ($payload['role'] ?? null),
            'account_id' => $token->account_id,
            'first_name' => $token->first_name,
            'last_name' => $token->last_name,
            'email' => $token->email,
            'enrollment_token_id' => $token->id,
        ]);
    }

    public function resolveRoster(EnrollmentToken $token): ?Model
    {
        return match ($token->roster_type) {
            'faculty' => AllowedFaculty::query()->find($token->roster_id),
            'administrator' => AllowedAdministrator::query()->find($token->roster_id),
            default => AllowedStudent::query()->find($token->roster_id),
        };
    }

    public function expirationHours(): int
    {
        return max(1, (int) config('enrollment.link_expiration_hours', 24));
    }

    protected function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * @return array{sent: bool, error: string|null}
     */
    protected function sendEnrollmentEmail(EnrollmentToken $token, string $enrollUrl, int $hours): array
    {
        try {
            Mail::to($token->email)->send(new RosterPasskeyEnrollmentMail(
                userName: trim($token->first_name.' '.$token->last_name),
                enrollmentUrl: $enrollUrl,
                expiresInHours: $hours,
            ));

            return ['sent' => true, 'error' => null];
        } catch (Throwable $exception) {
            report($exception);

            $detail = 'We could not send the email. Use the Continue button below, or contact your administrator.';
            if (config('app.debug')) {
                $detail .= ' ('.$exception->getMessage().')';
            }

            return ['sent' => false, 'error' => $detail];
        }
    }
}
