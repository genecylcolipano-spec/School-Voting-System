<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EnrollmentTokenService;
use App\Services\Auth\RegistrationAttemptService;
use App\Services\Auth\RosterVerificationService;
use App\Services\Auth\StudentRegistrationPasskeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * School portal account registration (no password).
 * Confirm & Validate against roster → enrollment token email → passkey → active account.
 */
class PortalRegistrationController extends Controller
{
    public function __construct(
        protected RosterVerificationService $roster,
        protected StudentRegistrationPasskeyService $registrationPasskeys,
        protected EnrollmentTokenService $enrollmentTokens,
        protected RegistrationAttemptService $attempts,
    ) {}

    public function create(): View|RedirectResponse
    {
        if (! \App\Models\SystemSetting::getValue('enable_student_registration', true)) {
            return redirect()
                ->route('login')
                ->with('status', 'Student registration is currently disabled by the school administrator.');
        }

        return view('auth.register', [
            'loginUrl' => route('login'),
            'expirationHours' => $this->enrollmentTokens->expirationHours(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! \App\Models\SystemSetting::getValue('enable_student_registration', true)) {
            return redirect()
                ->route('login')
                ->with('status', 'Student registration is currently disabled by the school administrator.');
        }

        $validated = $request->validate([
            'account_id' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        $accountId = trim($validated['account_id']);
        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);
        $email = strtolower(trim($validated['email']));

        $this->attempts->assertNotBlocked($request, $accountId);

        Log::debug('Portal registration validation attempt.', [
            'account_id' => $accountId,
            'ip' => $request->ip(),
        ]);

        try {
            $match = $this->roster->assertEligibleForRegistration($accountId, $firstName, $lastName);
        } catch (ValidationException $exception) {
            // Generic failure for roster mismatch; keep already-registered message as-is.
            $messages = $exception->errors();
            $accountErrors = $messages['account_id'] ?? [];
            $isAlreadyRegistered = in_array(RosterVerificationService::ALREADY_REGISTERED_MESSAGE, $accountErrors, true);

            if (! $isAlreadyRegistered) {
                $this->attempts->recordFailure($request, $accountId);
            }

            throw $exception;
        }

        $this->attempts->clear($request, $accountId);

        $issued = $this->enrollmentTokens->issueForMatch($match, $email, $firstName, $lastName);

        $request->session()->put(EnrollmentTokenService::SESSION_PLAIN_TOKEN, $issued['plain']);
        $request->session()->put(EnrollmentTokenService::SESSION_TOKEN_ID, $issued['token']->id);

        $status = $issued['email_sent']
            ? 'Identity verified. Check your email for the passkey setup link, or continue below.'
            : ($issued['email_error'] ?: 'Identity verified. Continue to passkey setup below.');

        return redirect()
            ->route('register.verified')
            ->with('status', $status)
            ->with('email_sent', $issued['email_sent']);
    }

    public function verified(Request $request): View|RedirectResponse
    {
        $plain = $request->session()->get(EnrollmentTokenService::SESSION_PLAIN_TOKEN);

        if (! is_string($plain) || $plain === '') {
            return redirect()->route('register');
        }

        $token = $this->enrollmentTokens->findByPlainToken($plain);

        if (! $token || ! $token->isUsable()) {
            $request->session()->forget([
                EnrollmentTokenService::SESSION_PLAIN_TOKEN,
                EnrollmentTokenService::SESSION_TOKEN_ID,
            ]);

            return redirect()->route('register.expired');
        }

        return view('auth.register-verified', [
            'loginUrl' => route('login'),
            'enrollUrl' => route('register.enroll', ['token' => $plain]),
            'expirationHours' => $this->enrollmentTokens->expirationHours(),
            'emailSent' => (bool) session('email_sent'),
            'email' => $token->email,
        ]);
    }

    public function enroll(Request $request, string $token): View|RedirectResponse
    {
        $record = $this->enrollmentTokens->findByPlainToken($token);

        if (! $record) {
            return redirect()->route('register.expired');
        }

        if ($record->isExpired() || $record->invalidated_at !== null || $record->used_at !== null) {
            return redirect()->route('register.expired');
        }

        if (! $record->isUsable()) {
            return redirect()->route('register.expired');
        }

        $roster = $this->enrollmentTokens->resolveRoster($record);
        if (! $roster || $roster->archived_at !== null || (method_exists($roster, 'isFullyRegistered') && $roster->isFullyRegistered())) {
            return redirect()->route('register.expired');
        }

        $pending = $this->enrollmentTokens->pendingPayloadFromToken($record);
        $this->registrationPasskeys->stashPendingRegistration($request, $pending);
        $request->session()->put(EnrollmentTokenService::SESSION_TOKEN_ID, $record->id);

        return view('auth.enroll-passkey', [
            'user' => null,
            'pending' => $pending,
            'registerOptionsUrl' => route('register.passkey.bootstrap.options'),
            'registerVerifyUrl' => route('register.passkey.bootstrap.verify'),
        ]);
    }

    public function expired(): View
    {
        return view('auth.register-expired', [
            'loginUrl' => route('login'),
            'registerUrl' => route('register'),
        ]);
    }

    public function passkeySetup(Request $request): View|RedirectResponse
    {
        // Legacy route: prefer token enroll URL when available.
        $plain = $request->session()->get(EnrollmentTokenService::SESSION_PLAIN_TOKEN);
        if (is_string($plain) && $plain !== '') {
            return redirect()->route('register.enroll', ['token' => $plain]);
        }

        if (! $this->registrationPasskeys->pendingRegistration($request)) {
            return redirect()->route('register');
        }

        $pending = $this->registrationPasskeys->pendingRegistration($request);

        return view('auth.enroll-passkey', [
            'user' => null,
            'pending' => $pending,
            'registerOptionsUrl' => route('register.passkey.bootstrap.options'),
            'registerVerifyUrl' => route('register.passkey.bootstrap.verify'),
        ]);
    }
}
