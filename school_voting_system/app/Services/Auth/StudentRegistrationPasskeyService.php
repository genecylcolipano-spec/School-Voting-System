<?php

namespace App\Services\Auth;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passkeys\Actions\StorePasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Support\WebAuthn;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

/**
 * Completes portal registration after roster verification + passkey ceremony.
 * Supports student, faculty, and administrator roster matches.
 */
class StudentRegistrationPasskeyService
{
    public const SESSION_PENDING = 'passkey.pending_registration';

    public function __construct(
        protected PendingRegistrationOptionsFactory $optionsFactory,
    ) {}

    /**
     * @param  array<string, mixed>  $pending
     */
    public function stashPendingRegistration(Request $request, array $pending): void
    {
        $request->session()->put(self::SESSION_PENDING, $pending);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pendingRegistration(Request $request): ?array
    {
        $pending = $request->session()->get(self::SESSION_PENDING);

        return is_array($pending) ? $pending : null;
    }

    public function clearPendingRegistration(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_PENDING,
            'passkey.registration_options',
        ]);
    }

    public function issueRegistrationOptions(Request $request): array
    {
        $pending = $this->pendingRegistration($request);

        if (! $pending) {
            abort(403, 'Registration session expired. Please register again.');
        }

        $options = $this->optionsFactory->make($pending);

        $request->session()->put(
            'passkey.registration_options',
            WebAuthn::toJson($options),
        );

        return WebAuthn::toBrowserArray($options);
    }

    /**
     * @return array{user: User, redirect: string}
     */
    public function completeRegistration(
        Request $request,
        PublicKeyCredential $credential,
        PublicKeyCredentialCreationOptions $options,
        StorePasskey $storePasskey,
        string $deviceName,
    ): array {
        $pending = $this->pendingRegistration($request);

        if (! $pending) {
            throw InvalidPasskeyException::make('Registration session expired. Please register again.');
        }

        return DB::transaction(function () use ($request, $credential, $options, $storePasskey, $deviceName, $pending) {
            [$rosterType, $roster] = $this->lockRosterRecord($pending);

            if (! $roster || $roster->is_registered || $roster->archived_at !== null) {
                throw InvalidPasskeyException::make(RosterVerificationService::ALREADY_REGISTERED_MESSAGE);
            }

            if (User::query()->where('account_id', $roster->account_id)->exists()) {
                throw InvalidPasskeyException::make(RosterVerificationService::ALREADY_REGISTERED_MESSAGE);
            }

            if (User::query()->where('email', $pending['email'])->exists()) {
                throw InvalidPasskeyException::make('The email has already been taken.');
            }

            $role = UserRole::tryFrom((string) ($pending['role'] ?? ''))
                ?? match ($rosterType) {
                    'faculty' => UserRole::Faculty,
                    'administrator' => UserRole::Admin,
                    default => UserRole::Student,
                };

            $fullName = trim(($pending['first_name'] ?? '').' '.($pending['last_name'] ?? ''));

            $attributes = [
                'account_id' => $roster->account_id,
                'name' => $fullName !== '' ? $fullName : trim($roster->first_name.' '.$roster->last_name),
                'first_name' => $pending['first_name'] ?? $roster->first_name,
                'last_name' => $pending['last_name'] ?? $roster->last_name,
                'email' => $pending['email'],
                'password' => null,
                'role' => $role,
                'is_active' => true,
            ];

            if ($role === UserRole::Student) {
                $attributes['grade_level'] = $roster->grade_level ?? ($pending['grade_level'] ?? null);
                $attributes['section'] = $roster->section ?? ($pending['section'] ?? null);
                $attributes['student_status'] = StudentStatus::Enrolled;
            }

            $user = User::query()->create($attributes);

            $storePasskey($user, $deviceName, $credential, $options);

            if (method_exists($roster, 'markFullyRegistered')) {
                $roster->markFullyRegistered();
            } else {
                $roster->forceFill(['is_registered' => true])->save();
            }

            if (! empty($pending['enrollment_token_id'])) {
                $enrollmentToken = \App\Models\EnrollmentToken::query()
                    ->lockForUpdate()
                    ->find($pending['enrollment_token_id']);

                if ($enrollmentToken && $enrollmentToken->used_at === null) {
                    $enrollmentToken->markUsed();
                }
            }

            event(new Registered($user));

            Auth::login($user);
            $request->session()->regenerate();
            $this->clearPendingRegistration($request);
            $request->session()->forget([
                \App\Services\Auth\EnrollmentTokenService::SESSION_PLAIN_TOKEN,
                \App\Services\Auth\EnrollmentTokenService::SESSION_TOKEN_ID,
            ]);

            Log::info('Roster registration completed.', [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'role' => $user->role?->value,
                'roster_type' => $rosterType,
                'roster_id' => $roster->getKey(),
            ]);

            $notifications = app(\App\Services\Portal\PortalNotificationService::class);
            if ($user->isStudent()) {
                $notifications->studentRegistered($user);
                $notifications->studentPasskeyRegistered($user);
            }

            return [
                'user' => $user,
                'redirect' => app(RoleRedirectService::class)->dashboardPathFor($user),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $pending
     * @return array{0: string, 1: (AllowedStudent|AllowedFaculty|AllowedAdministrator|null)}
     */
    protected function lockRosterRecord(array $pending): array
    {
        $rosterType = (string) ($pending['roster_type'] ?? 'student');
        $rosterId = (int) ($pending['roster_id'] ?? $pending['allowed_student_id'] ?? 0);

        $resolvedType = match ($rosterType) {
            'faculty' => 'faculty',
            'administrator' => 'administrator',
            default => 'student',
        };

        $model = match ($resolvedType) {
            'faculty' => AllowedFaculty::class,
            'administrator' => AllowedAdministrator::class,
            default => AllowedStudent::class,
        };

        /** @var Model|null $roster */
        $roster = $model::query()->lockForUpdate()->find($rosterId);

        return [$resolvedType, $roster];
    }

    public static function ensureCredentialIsUnique(string $credentialId): void
    {
        $exists = \Laravel\Passkeys\Passkeys::passkeyModel()::query()
            ->where('credential_id', $credentialId)
            ->exists();

        if ($exists) {
            throw InvalidPasskeyException::make('Unable to register this passkey.');
        }
    }

    public static function encodeCredentialId(string $rawCredentialId): string
    {
        return Base64UrlSafe::encodeUnpadded($rawCredentialId);
    }
}
