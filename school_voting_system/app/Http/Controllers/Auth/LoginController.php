<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use App\Services\Auth\StudentRegistrationPasskeyService;
use App\Services\SuperAdmin\AuditLogService;
use App\Enums\AuditActionType;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Http\Requests\PasskeyRegistrationRequest;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use RuntimeException;
use Throwable;

/**
 * Passkey login controller — exact account_id matching, role-aware redirects, debug logging.
 */
class LoginController extends Controller
{
    public function __construct(
        protected RoleRedirectService $redirects,
        protected AuditLogService $audit,
        protected StudentRegistrationPasskeyService $studentRegistrationPasskeys,
    ) {}

    public function showLogin()
    {
        return view('auth.login', [
            'loginOptionsUrl' => route('login.options'),
            'loginVerifyUrl' => route('login.verify'),
        ]);
    }

    /**
     * Login ceremony (step 1): issue a random challenge stored in session.
     */
    public function loginOptions(Request $request, \Laravel\Passkeys\Actions\GenerateVerificationOptions $generate): JsonResponse
    {
        try {
            $options = $generate();
            $request->session()->put(
                'passkey.verification_options',
                WebAuthn::toJson($options)
            );

            Log::debug('Passkey login options issued.', [
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'user_agent' => $request->userAgent(),
                'rp_id' => Passkeys::relyingPartyId(),
            ]);

            return response()->json([
                'options' => WebAuthn::toBrowserArray($options),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to start passkey authentication. Please try again.',
            ], 500);
        }
    }

    /**
     * Login ceremony (step 2): verify signed assertion, update counter, establish session.
     */
    public function loginVerify(
        PasskeyVerificationRequest $request,
        VerifyPasskey $verify,
    ): JsonResponse {
        $clientIp = (string) $request->ip();

        try {
            return DB::transaction(function () use ($request, $verify, $clientIp) {
                $credential = $request->credential();

                Log::debug('Passkey login verify started.', [
                    'ip' => $clientIp,
                    'host' => $request->getHost(),
                    'credential_type' => $credential->type,
                    'rp_id' => Passkeys::relyingPartyId(),
                ]);

                $passkey = $verify(
                    $credential,
                    $request->verificationOptions()
                );

                $user = $passkey->user;

                if (! $user instanceof User) {
                    Log::warning('Passkey login rejected: user model mismatch.', [
                        'passkey_id' => $passkey->id,
                        'user_id' => $passkey->user_id,
                        'ip' => $clientIp,
                    ]);

                    throw InvalidPasskeyException::make('Passkey not recognized. It may have been removed from your account.');
                }

                Log::debug('Passkey credential matched.', [
                    'passkey_id' => $passkey->id,
                    'stored_credential_id' => $passkey->credential_id,
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'role' => $user->role?->value,
                    'ip' => $clientIp,
                ]);

                if (! Passkeys::allowsLogin($request, $passkey)) {
                    Log::warning('Passkey login blocked by authorization hook.', [
                        'user_id' => $user->id,
                        'account_id' => $user->account_id,
                        'role' => $user->role?->value,
                        'ip' => $clientIp,
                    ]);

                    throw InvalidPasskeyException::make('This account is not permitted to access the portal.');
                }

                $guard = Auth::guard(Config::string('passkeys.guard'));

                if (! $guard instanceof StatefulGuard) {
                    throw new RuntimeException('Passkeys requires a stateful authentication guard.');
                }

                $guard->login($user, $request->remember());
                $request->session()->regenerate();
                $request->session()->put('authenticated_passkey_id', $passkey->id);

                $redirect = $this->redirects->dashboardPathFor($user);

                Log::info('Passkey login succeeded.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'role' => $user->role?->value,
                    'redirect' => $redirect,
                    'ip' => $clientIp,
                ]);

                $this->audit->record($user, 'Passkey login succeeded', AuditActionType::Auth, metadata: ['redirect' => $redirect]);

                return response()->json([
                    'message' => "Welcome back, {$user->name}.",
                    'redirect' => $redirect,
                ]);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidPasskeyException $exception) {
            Log::warning('Passkey login failed: credential not recognized.', [
                'message' => $exception->getMessage(),
                'ip' => $clientIp,
                'host' => $request->getHost(),
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Passkey login failed: unexpected error.', [
                'message' => $exception->getMessage(),
                'ip' => $clientIp,
            ]);

            return response()->json([
                'message' => 'Passkey verification failed. Please try again or contact an administrator.',
            ], 422);
        }
    }

    /**
     * Registration ceremony (step 1): challenge for authenticated user.
     */
    public function registerOptions(Request $request, GenerateRegistrationOptions $generate): JsonResponse
    {
        try {
            if ($this->studentRegistrationPasskeys->pendingRegistration($request)) {
                $pending = $this->studentRegistrationPasskeys->pendingRegistration($request);

                Log::debug('Passkey registration options issued for pending student signup.', [
                    'account_id' => $pending['account_id'] ?? null,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'options' => $this->studentRegistrationPasskeys->issueRegistrationOptions($request),
                ]);
            }

            $user = $this->resolveRegistrationUser($request);

            Log::debug('Passkey registration options issued.', [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'role' => $user->role?->value,
                'ip' => $request->ip(),
            ]);

            $options = $generate($user);
            $request->session()->put(
                'passkey.registration_options',
                WebAuthn::toJson($options)
            );

            return response()->json([
                'options' => WebAuthn::toBrowserArray($options),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to prepare passkey registration.',
            ], 500);
        }
    }

    /**
     * Registration ceremony (step 2): persist attested credential.
     */
    public function registerVerify(
        PasskeyRegistrationRequest $request,
        \Laravel\Passkeys\Actions\StorePasskey $storePasskey,
    ): JsonResponse {
        try {
            $deviceName = $request->string('name')->toString()
                ?: $request->string('device_name')->toString()
                ?: 'Primary Device';

            if ($this->studentRegistrationPasskeys->pendingRegistration($request)) {
                $pending = $this->studentRegistrationPasskeys->pendingRegistration($request);

                Log::debug('Passkey registration verify started for pending student signup.', [
                    'account_id' => $pending['account_id'] ?? null,
                    'device_name' => $deviceName,
                    'ip' => $request->ip(),
                ]);

                $result = $this->studentRegistrationPasskeys->completeRegistration(
                    $request,
                    $request->credential(),
                    $request->registrationOptions(),
                    $storePasskey,
                    $deviceName,
                );

                $user = $result['user'];

                Log::info('Student passkey registration completed.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'role' => $user->role?->value,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Passkey registered successfully.',
                    'redirect' => $result['redirect'],
                ], 201);
            }

            return DB::transaction(function () use ($request, $storePasskey, $deviceName) {
                $user = $this->resolveRegistrationUser($request);

                Log::debug('Passkey registration verify started.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'role' => $user->role?->value,
                    'device_name' => $deviceName,
                    'ip' => $request->ip(),
                ]);

                $passkey = $storePasskey(
                    $user,
                    $deviceName,
                    $request->credential(),
                    $request->registrationOptions()
                );

                if ($request->session()->pull('passkey.bootstrap_user_id')) {
                    Auth::login($user);
                    $request->session()->regenerate();
                }

                Log::info('Passkey registered successfully.', [
                    'passkey_id' => $passkey->id,
                    'credential_id' => $passkey->credential_id,
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'role' => $user->role?->value,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Passkey registered successfully.',
                    'redirect' => $this->redirects->dashboardPathFor($user),
                    'passkey' => [
                        'id' => $passkey->id,
                        'device_name' => $passkey->device_name ?? $passkey->name,
                        'counter' => $passkey->counter,
                    ],
                ], 201);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidPasskeyException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Passkey registration failed. Please try again.',
            ], 422);
        }
    }

    /**
     * Look up a portal account by exact account_id (case-sensitive, no trimming).
     */
    public static function findAccountById(string $accountId): ?User
    {
        return User::query()
            ->where('account_id', $accountId)
            ->first();
    }

    protected function resolveRegistrationUser(Request $request): User
    {
        // Prefer enrollment target over the currently authenticated user.
        $bootstrapUserId = $request->session()->get('passkey.bootstrap_user_id');

        if ($bootstrapUserId) {
            $user = User::query()->find($bootstrapUserId);

            if ($user) {
                return $user;
            }
        }

        if ($request->user() instanceof User) {
            return $request->user();
        }

        throw new AuthenticationException;
    }
}
