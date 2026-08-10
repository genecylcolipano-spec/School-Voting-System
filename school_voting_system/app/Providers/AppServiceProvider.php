<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\Partylist;
use App\Models\User;
use App\Policies\CandidatePolicy;
use App\Policies\ElectionPolicy;
use App\Policies\PortalContentPolicy;
use App\Policies\UserPolicy;
use App\Actions\Passkeys\GeneratePlatformRegistrationOptions;
use App\Actions\Passkeys\GeneratePlatformVerificationOptions;
use App\Actions\Passkeys\StorePasskeyCredential;
use App\Actions\Passkeys\VerifyPasskeyCredential;
use App\Http\Responses\PasskeyLoginJsonResponse;
use App\Models\Passkey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\StorePasskey;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey as BasePasskey;
use Laravel\Passkeys\Passkeys;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passkeys::ignoreRoutes();

        $this->app->singleton(PasskeyLoginResponse::class, PasskeyLoginJsonResponse::class);
        $this->app->bind(GenerateRegistrationOptions::class, GeneratePlatformRegistrationOptions::class);
        $this->app->bind(\Laravel\Passkeys\Actions\GenerateVerificationOptions::class, GeneratePlatformVerificationOptions::class);
        $this->app->bind(StorePasskey::class, StorePasskeyCredential::class);
        $this->app->bind(VerifyPasskey::class, VerifyPasskeyCredential::class);
    }

    public function boot(): void
    {
        Passkeys::useUserModel(User::class);
        Passkeys::usePasskeyModel(Passkey::class);

        Passkeys::authorizeLoginUsing(function (Request $request, PasskeyUser $user, BasePasskey $passkey): bool {
            if (! $user instanceof User) {
                Log::warning('Passkey authorizeLogin rejected: not a User model.', [
                    'passkey_id' => $passkey->id,
                    'ip' => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'credential' => ['This account is not authorized to access the portal.'],
                ]);
            }

            if (! $user->role) {
                Log::warning('Passkey authorizeLogin rejected: missing role.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'ip' => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'credential' => ['This account is not authorized to access the portal.'],
                ]);
            }

            if (! $user->is_active || $user->archived_at !== null) {
                Log::warning('Passkey authorizeLogin rejected: inactive or archived account.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'ip' => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'credential' => ['This account has been deactivated. Contact your administrator.'],
                ]);
            }

            if ($passkey instanceof Passkey && ! $passkey->isUsable()) {
                Log::warning('Passkey authorizeLogin rejected: credential not usable.', [
                    'user_id' => $user->id,
                    'account_id' => $user->account_id,
                    'passkey_id' => $passkey->id,
                    'status' => $passkey->status?->value,
                    'ip' => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'credential' => ['This passkey is no longer valid. Request a passkey reset or use another registered device.'],
                ]);
            }

            Log::debug('Passkey authorizeLogin approved.', [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'role' => $user->role->value,
                'ip' => $request->ip(),
            ]);

            return true;
        });

        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::policy(Candidate::class, CandidatePolicy::class);
        Gate::policy(Event::class, PortalContentPolicy::class);
        Gate::policy(Announcement::class, PortalContentPolicy::class);
        Gate::policy(Fundraiser::class, PortalContentPolicy::class);
        Gate::policy(Partylist::class, PortalContentPolicy::class);
        Gate::define('viewAnyStudents', [UserPolicy::class, 'viewAnyStudents']);
        Gate::define('issuePasskeyReset', [UserPolicy::class, 'issuePasskeyReset']);
        Gate::define('updateStudentRecord', [UserPolicy::class, 'updateStudentRecord']);
        Gate::define('importStudentRecords', [UserPolicy::class, 'importStudentRecords']);
    }
}
