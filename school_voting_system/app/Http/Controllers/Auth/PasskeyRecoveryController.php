<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IssuePasskeyResetRequest;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use App\Services\Auth\PasskeyRecoveryTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Fallback recovery when a user loses access to all registered passkeys.
 * Self-service: hashed single-use token emailed when Account ID + email match.
 * Admin path: signed enrollment link via PasskeyEnrollmentLinkService.
 */
class PasskeyRecoveryController extends Controller
{
    public function __construct(
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
        protected PasskeyRecoveryTokenService $recoveryTokens,
    ) {}

    public function show(): View
    {
        return view('auth.recovery', [
            'loginUrl' => route('login'),
            'supportEmail' => config('mail.from.address'),
            'resetExpirationMinutes' => $this->recoveryTokens->expirationMinutes(),
        ]);
    }

    public function requestReset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $accountId = trim($validated['account_id']);
        $email = strtolower(trim($validated['email']));
        $ip = (string) $request->ip();

        Log::info('Passkey recovery request received.', [
            'account_id' => $accountId,
            'ip' => $ip,
        ]);

        $genericMessage = 'If your account details are valid, a passkey reset link has been sent to the registered email address.';

        $accountLimiterKey = 'passkey-recovery-account:'.sha1(strtolower($accountId).'|'.$email);
        if (RateLimiter::tooManyAttempts($accountLimiterKey, 3)) {
            Log::warning('Passkey recovery request throttled (account).', [
                'account_id' => $accountId,
                'ip' => $ip,
            ]);

            return response()->json([
                'message' => 'Too many reset requests. Please wait a few minutes and try again.',
            ], 429);
        }

        RateLimiter::hit($accountLimiterKey, 600);

        $user = User::query()
            ->where('account_id', $accountId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            // Enumeration-safe audit row (no token).
            PasskeyRecoveryRequest::query()->create([
                'user_id' => null,
                'account_id' => $accountId,
                'email' => $email,
                'status' => PasskeyRecoveryRequest::STATUS_PENDING,
                'requested_ip' => $ip,
                'requested_user_agent' => (string) $request->userAgent(),
            ]);

            Log::info('Passkey recovery request rejected.', [
                'account_id' => $accountId,
                'outcome' => 'no_match',
                'ip' => $ip,
            ]);

            return response()->json(['message' => $genericMessage]);
        }

        $cooldownKey = 'passkey-recovery-cooldown:'.$user->id;
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            Log::info('Passkey recovery request rejected.', [
                'account_id' => $accountId,
                'user_id' => $user->id,
                'outcome' => 'cooldown',
                'ip' => $ip,
            ]);

            return response()->json(['message' => $genericMessage]);
        }

        $issued = $this->recoveryTokens->issueAndEmail(
            $user,
            $email,
            $ip,
            (string) $request->userAgent(),
        );

        if (! $issued['email_sent']) {
            return response()->json([
                'message' => 'We could not deliver a reset email right now. Please try again later or contact an administrator.',
                'delivery_failed' => true,
            ], 503);
        }

        RateLimiter::hit($cooldownKey, 120);

        return response()->json(['message' => $genericMessage]);
    }

    /**
     * Consume a hashed recovery token and start bootstrap passkey enrollment.
     */
    public function continueWithToken(Request $request, string $token): View|RedirectResponse
    {
        $recovery = $this->recoveryTokens->findByPlainToken($token);

        if (! $recovery) {
            Log::info('Passkey recovery token rejected.', [
                'outcome' => 'invalid',
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login.recovery')->with(
                'status',
                'The link is no longer valid.',
            );
        }

        $failure = $recovery->tokenFailureReason();
        if ($failure !== null) {
            Log::info('Passkey recovery token rejected.', [
                'recovery_request_id' => $recovery->id,
                'outcome' => $failure === 'expired' ? 'expired' : 'used',
                'ip' => $request->ip(),
            ]);

            $message = $failure === 'expired'
                ? 'The reset link has expired.'
                : 'The link is no longer valid.';

            return redirect()->route('login.recovery')->with('status', $message);
        }

        $user = $recovery->user;
        if (! $user) {
            return redirect()->route('login.recovery')->with('status', 'The link is no longer valid.');
        }

        if ($request->user()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('passkey.bootstrap_user_id', $user->id);
        $request->session()->put(PasskeyRecoveryTokenService::SESSION_RECOVERY_REQUEST_ID, $recovery->id);

        Log::info('Passkey recovery token accepted for enrollment.', [
            'recovery_request_id' => $recovery->id,
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'ip' => $request->ip(),
        ]);

        return view('auth.enroll-passkey', [
            'user' => $user,
            'pending' => null,
            'registerOptionsUrl' => route('register.passkey.bootstrap.options'),
            'registerVerifyUrl' => route('register.passkey.bootstrap.verify'),
        ]);
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
        $expiresInMinutes = max(60, (int) config('enrollment.link_expiration_hours', 24) * 60);

        if ($recoveryRequest) {
            $recoveryRequest->forceFill([
                'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
                'resolved_by' => $request->user()?->id,
                'resolved_at' => now(),
            ])->save();
        }

        $result = $this->enrollmentLinks->sendToUser($user, $recipientEmail, $expiresInMinutes);
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
                'expires_in_minutes' => $expiresInMinutes,
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
