<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\GenerateVerificationOptions;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Http\Requests\PasskeyRegistrationRequest;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use RuntimeException;
use Throwable;

/**
 * Thin WebAuthn ceremony controller backed by laravel/passkeys (webauthn-framework).
 */
class PasskeyAuthController extends Controller
{
    public function __construct(protected RoleRedirectService $redirects) {}

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
    public function loginOptions(Request $request, GenerateVerificationOptions $generate): JsonResponse
    {
        try {
            $options = $generate();
            $request->session()->put(
                'passkey.verification_options',
                WebAuthn::toJson($options)
            );

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
        try {
            return DB::transaction(function () use ($request, $verify) {
                $passkey = $verify(
                    $request->credential(),
                    $request->verificationOptions()
                );

                if (! Passkeys::allowsLogin($request, $passkey)) {
                    throw InvalidPasskeyException::make('This account is not permitted to access the portal.');
                }

                $guard = Auth::guard(Config::string('passkeys.guard'));

                if (! $guard instanceof StatefulGuard) {
                    throw new RuntimeException('Passkeys requires a stateful authentication guard.');
                }

                $guard->login($passkey->user, $request->remember());
                $request->session()->regenerate();
                $request->session()->put('authenticated_passkey_id', $passkey->id);

                return response()->json([
                    'message' => "Welcome back, {$passkey->user->name}.",
                    'redirect' => $this->redirects->dashboardPathFor($passkey->user),
                ]);
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
            $user = $this->resolveRegistrationUser($request);

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
            return DB::transaction(function () use ($request, $storePasskey) {
                $user = $this->resolveRegistrationUser($request);

                $deviceName = $request->string('name')->toString()
                    ?: $request->string('device_name')->toString()
                    ?: 'Primary Device';

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
     * Placeholder for admin-driven recovery / passkey reset workflows.
     */
    public function recoveryNotice(): JsonResponse
    {
        return response()->json([
            'message' => 'Contact a school administrator or super admin to reset your passkey profile.',
            'support_email' => config('mail.from.address'),
        ]);
    }

    protected function resolveRegistrationUser(Request $request): User
    {
        if ($request->user() instanceof User) {
            return $request->user();
        }

        $bootstrapUserId = $request->session()->get('passkey.bootstrap_user_id');

        if ($bootstrapUserId) {
            $user = User::query()->find($bootstrapUserId);

            if ($user) {
                return $user;
            }
        }

        throw new AuthenticationException;
    }
}
