<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\RosterVerificationService;
use App\Services\Auth\StudentRegistrationPasskeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * School portal account registration (no password).
 * Roster verification first, then passkey enrollment, then user creation.
 */
class PortalRegistrationController extends Controller
{
    public function __construct(
        protected RosterVerificationService $roster,
        protected StudentRegistrationPasskeyService $registrationPasskeys,
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

        Log::debug('Portal registration attempt.', [
            'account_id' => $accountId,
            'ip' => $request->ip(),
        ]);

        $match = $this->roster->assertEligibleForRegistration($accountId, $firstName, $lastName);

        $this->registrationPasskeys->stashPendingRegistration(
            $request,
            $match->toPendingPayload(
                strtolower(trim($validated['email'])),
                $firstName,
                $lastName,
            ),
        );

        return redirect()
            ->route('register.passkey.setup')
            ->with('status', 'Identity verified. Register your passkey to complete signup.');
    }

    public function passkeySetup(Request $request): View|RedirectResponse
    {
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
