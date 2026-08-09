<?php

namespace App\Services\Auth;

use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class PasskeyEnrollmentLinkService
{
    public function createSignedUrl(User $user, int $expiresInMinutes = 120): string
    {
        return URL::temporarySignedRoute(
            'register.passkey.bootstrap',
            now()->addMinutes($expiresInMinutes),
            ['user' => $user->id],
        );
    }

    /**
     * @return array{url: string, email_sent: bool, email_error: string|null, recipient: string|null}
     */
    public function sendToUser(User $user, ?string $recipientEmail = null, int $expiresInMinutes = 120): array
    {
        $url = $this->createSignedUrl($user, $expiresInMinutes);
        $recipient = $recipientEmail ?: $user->email;

        if (! filled($recipient)) {
            return [
                'url' => $url,
                'email_sent' => false,
                'email_error' => 'No email address on file.',
                'recipient' => null,
            ];
        }

        try {
            Mail::to($recipient)->send(new PasskeyResetEnrollmentLinkMail(
                userName: $user->name,
                enrollmentUrl: $url,
                expiresInMinutes: $expiresInMinutes,
            ));

            return [
                'url' => $url,
                'email_sent' => true,
                'email_error' => null,
                'recipient' => $recipient,
            ];
        } catch (Throwable $exception) {
            report($exception);

            $detail = 'Email delivery failed. Share the link manually.';

            if (config('app.debug')) {
                $detail .= ' ('.$exception->getMessage().')';
            }

            return [
                'url' => $url,
                'email_sent' => false,
                'email_error' => $detail,
                'recipient' => $recipient,
            ];
        }
    }
}
