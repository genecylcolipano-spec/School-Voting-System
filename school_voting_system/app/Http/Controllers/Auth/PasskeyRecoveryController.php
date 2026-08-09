<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IssuePasskeyResetRequest;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Fallback recovery when a user loses access to all registered passkeys.
 * Production flow: admin/super-admin issues a new signed enrollment link.
 */
class PasskeyRecoveryController extends Controller
{
    public function __construct(protected PasskeyEnrollmentLinkService $enrollmentLinks) {}

    public function show(): View
    {
        return view('auth.recovery', [
            'loginUrl' => route('login'),
            'supportEmail' => config('mail.from.address'),
        ]);
    }

    public function requestReset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email'],
        ]);

        $accountId = $validated['account_id'];

        Log::debug('Passkey recovery lookup.', [
            'account_id' => $accountId,
            'account_id_bytes' => bin2hex($accountId),
            'email' => $validated['email'],
            'ip' => $request->ip(),
        ]);

        $user = User::query()
            ->where('account_id', $accountId)
            ->where('email', $validated['email'])
            ->first();

        // Uniform response prevents account enumeration.
        $message = 'If your details match our records, a passkey reset link will be sent to your email.';

        $recoveryRequest = PasskeyRecoveryRequest::query()->create([
            'user_id' => $user?->id,
            'account_id' => $accountId,
            'email' => $validated['email'],
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
            'requested_ip' => (string) $request->ip(),
            'requested_user_agent' => (string) $request->userAgent(),
        ]);

        if ($user) {
            $recentResolved = PasskeyRecoveryRequest::query()
                ->where('user_id', $user->id)
                ->where('status', PasskeyRecoveryRequest::STATUS_RESOLVED)
                ->whereNotNull('resolved_at')
                ->where('resolved_at', '>=', now()->subMinutes(2))
                ->latest('resolved_at')
                ->first();

            if ($recentResolved) {
                $recoveryRequest->forceFill([
                    'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
                    // Keep original send time; do not extend cooldown window on retries.
                    'resolved_at' => $recentResolved->resolved_at,
                ])->save();

                Log::info('Passkey recovery request processed (cooldown skipped send).', [
                    'recovery_request_id' => $recoveryRequest->id,
                    'account_id' => $accountId,
                    'email' => $validated['email'],
                    'user_id' => $user->id,
                    'outcome' => 'cooldown_skipped',
                    'last_sent_at' => $recentResolved->resolved_at?->toDateTimeString(),
                    'ip' => $request->ip(),
                ]);

                return response()->json(['message' => $message]);
            }

            try {
                $result = $this->enrollmentLinks->sendToUser($user, $validated['email']);

                $recoveryRequest->forceFill([
                    'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
                    'resolved_at' => now(),
                ])->save();

                Log::info('Passkey recovery request processed (auto-email sent).', [
                    'recovery_request_id' => $recoveryRequest->id,
                    'account_id' => $accountId,
                    'email' => $validated['email'],
                    'user_id' => $user->id,
                    'outcome' => $result['email_sent'] ? 'email_sent' : 'email_failed',
                    'ip' => $request->ip(),
                ]);

                if (! $result['email_sent']) {
                    throw new \RuntimeException($result['email_error'] ?? 'Email delivery failed.');
                }
            } catch (\Throwable $exception) {
                report($exception);

                Log::warning('Passkey recovery request processed (auto-email failed).', [
                    'recovery_request_id' => $recoveryRequest->id,
                    'account_id' => $accountId,
                    'email' => $validated['email'],
                    'user_id' => $user->id,
                    'outcome' => 'email_failed',
                    'ip' => $request->ip(),
                ]);

                // Keep request pending for admin follow-up when auto-email fails.
            }
        } else {
            Log::info('Passkey recovery request processed (no matching account).', [
                'recovery_request_id' => $recoveryRequest->id,
                'account_id' => $accountId,
                'email' => $validated['email'],
                'outcome' => 'no_match',
                'ip' => $request->ip(),
            ]);
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Admin-only: issue a fresh passkey enrollment link for a user.
     */
    public function issueEnrollmentLink(IssuePasskeyResetRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $recoveryRequest = null;
        $recoveryRequestId = $request->integer('recovery_request_id');

        if ($recoveryRequestId > 0) {
            $recoveryRequest = PasskeyRecoveryRequest::query()
                ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
                ->find($recoveryRequestId);
        }

        $recipientEmail = $recoveryRequest?->email ?: $user->email;

        if ($recoveryRequest) {
            $recoveryRequest->forceFill([
                'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
                'resolved_by' => $request->user()?->id,
                'resolved_at' => now(),
            ])->save();
        }

        $result = $this->enrollmentLinks->sendToUser($user, $recipientEmail);
        $emailSent = $result['email_sent'];
        $emailError = $result['email_error']
            ? 'Enrollment link created, but email delivery failed. Share the copied link manually.'
            : null;

        if ($request->user()) {
            app(\App\Services\Portal\PortalNotificationService::class)
                ->passkeyResetCompleted($user, $request->user());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Enrollment link generated.',
                'enrollment_url' => $result['url'],
                'expires_in_minutes' => 120,
                'email_sent' => $emailSent,
                'email_error' => $emailError,
            ]);
        }

        if ($emailSent) {
            return back()->with('success', "Enrollment link emailed to {$recipientEmail}.");
        }

        return back()
            ->with('success', 'Enrollment link generated. Copy it below if email delivery failed.')
            ->with('enrollment_url', $result['url'])
            ->with('error', $emailError);
    }
}
