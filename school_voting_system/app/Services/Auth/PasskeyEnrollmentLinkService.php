<?php

namespace App\Services\Auth;

use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\PasskeyEnrollmentLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class PasskeyEnrollmentLinkService
{
    public const SESSION_LINK_ID = 'passkey.enrollment_link_id';

    public const SESSION_DELIVERY = 'passkey.enrollment_delivery';

    public function expirationMinutes(?int $expiresInMinutes = null): int
    {
        return $expiresInMinutes ?? max(60, (int) config('enrollment.link_expiration_hours', 24) * 60);
    }

    /**
     * @return array{link: PasskeyEnrollmentLink, url: string, expires_in_minutes: int}
     */
    public function issueForUser(User $user, ?User $actor = null, ?int $expiresInMinutes = null): array
    {
        $expiresInMinutes = $this->expirationMinutes($expiresInMinutes);

        $this->invalidateActiveForUser($user->id);

        $plain = Str::random(32);

        $link = PasskeyEnrollmentLink::query()->create([
            'user_id' => $user->id,
            'issued_by' => $actor?->id,
            'token_hash' => $this->hash($plain),
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);

        $url = route('enroll.passkey.token', ['token' => $plain]);

        $this->rememberDelivery($user, $url);

        return [
            'link' => $link,
            'url' => $url,
            'expires_in_minutes' => $expiresInMinutes,
        ];
    }

    public function createSignedUrl(User $user, ?int $expiresInMinutes = null): string
    {
        return URL::temporarySignedRoute(
            'register.passkey.bootstrap',
            now()->addMinutes($this->expirationMinutes($expiresInMinutes)),
            ['user' => $user->id],
        );
    }

    /**
     * @return array{
     *     url: string,
     *     email_sent: bool,
     *     email_error: string|null,
     *     recipient: string|null,
     *     expires_in_minutes: int
     * }
     */
    public function sendToUser(User $user, ?string $recipientEmail = null, ?int $expiresInMinutes = null, ?User $actor = null): array
    {
        $issued = $this->issueForUser($user, $actor, $expiresInMinutes);
        $recipient = $recipientEmail ?: $user->email;

        $payload = [
            'url' => $issued['url'],
            'expires_in_minutes' => $issued['expires_in_minutes'],
            'recipient' => filled($recipient) ? $recipient : null,
        ];

        if (! filled($recipient)) {
            return array_merge($payload, [
                'email_sent' => false,
                'email_error' => 'No email address on file.',
            ]);
        }

        try {
            Mail::to($recipient)->send(new PasskeyResetEnrollmentLinkMail(
                userName: $user->name,
                enrollmentUrl: $issued['url'],
                expiresInMinutes: $issued['expires_in_minutes'],
                recoveryRequestId: null,
                selfService: false,
            ));

            return array_merge($payload, [
                'email_sent' => true,
                'email_error' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $detail = 'Email delivery failed. Share the link manually.';

            if (config('app.debug')) {
                $detail .= ' ('.$exception->getMessage().')';
            }

            return array_merge($payload, [
                'email_sent' => false,
                'email_error' => $detail,
            ]);
        }
    }

    public function findUsableByPlainToken(string $plain): ?PasskeyEnrollmentLink
    {
        if ($plain === '') {
            return null;
        }

        $link = PasskeyEnrollmentLink::query()
            ->where('token_hash', $this->hash($plain))
            ->first();

        if (! $link || ! $link->isUsable()) {
            return null;
        }

        return $link;
    }

    public function rememberDelivery(User $user, string $url): void
    {
        session([
            self::SESSION_DELIVERY => [
                'user_id' => $user->id,
                'url' => $url,
            ],
        ]);
    }

    public function rememberedUrlFor(User $user): ?string
    {
        $payload = session(self::SESSION_DELIVERY);

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
            return null;
        }

        $url = $payload['url'] ?? null;

        return filled($url) ? (string) $url : null;
    }

    public function markUsedFromSession(Request $request, User $user): void
    {
        $linkId = $request->session()->pull(self::SESSION_LINK_ID);

        if (! $linkId) {
            return;
        }

        $link = PasskeyEnrollmentLink::query()->find($linkId);

        if ($link && (int) $link->user_id === (int) $user->id) {
            $link->markUsed();
        }
    }

    public function invalidateActiveForUser(int $userId): void
    {
        PasskeyEnrollmentLink::query()
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => now()]);
    }

    protected function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
