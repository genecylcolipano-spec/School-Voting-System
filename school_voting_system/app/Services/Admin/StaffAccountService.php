<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\StaffRole;
use App\Models\User;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffAccountService
{
    public function __construct(
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
        protected PortalNotificationService $notifications,
    ) {}

    /**
     * @param  array{
     *     account_id: string,
     *     name: string,
     *     email: string,
     *     staff_role_id?: int|null,
     *     send_enrollment_email?: bool
     * }  $data
     * @return array{user: User, enrollment_url: string, email_sent: bool, email_error: string|null}
     */
    public function create(UserRole $role, array $data, User $actor): array
    {
        if (! in_array($role, [UserRole::Admin, UserRole::Faculty], true)) {
            throw ValidationException::withMessages([
                'role' => ['Only Administrator and Faculty accounts can be created here.'],
            ]);
        }

        $accountId = trim($data['account_id']);
        $email = strtolower(trim($data['email']));
        $name = trim($data['name']);

        if (User::query()->where('account_id', $accountId)->exists()) {
            throw ValidationException::withMessages([
                'account_id' => ['This account ID is already in use.'],
            ]);
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email is already in use.'],
            ]);
        }

        $staffRoleId = null;

        if ($role === UserRole::Admin && ! empty($data['staff_role_id'])) {
            $staffRoleId = StaffRole::query()
                ->whereKey($data['staff_role_id'])
                ->where('slug', '!=', 'chief_super_admin')
                ->value('id');
        }

        $user = DB::transaction(function () use ($role, $accountId, $name, $email, $staffRoleId) {
            $created = User::query()->create([
                'account_id' => $accountId,
                'name' => $name,
                'email' => $email,
                // Password unused for passkey auth; random hash keeps DB NOT NULL constraints happy.
                'password' => Hash::make(Str::random(64)),
                'role' => $role,
                'staff_role_id' => $staffRoleId,
                'is_active' => true,
            ]);

            if ($role === UserRole::Faculty) {
                AllowedFaculty::query()
                    ->where('account_id', $accountId)
                    ->where('is_registered', false)
                    ->get()
                    ->each(function (AllowedFaculty $row) {
                        $row->markFullyRegistered();
                    });
            }

            if ($role === UserRole::Admin) {
                AllowedAdministrator::query()
                    ->where('account_id', $accountId)
                    ->where('is_registered', false)
                    ->get()
                    ->each(function (AllowedAdministrator $row) {
                        $row->markFullyRegistered();
                    });
            }

            return $created;
        });

        if ($role === UserRole::Admin) {
            $this->notifications->administratorCreated($user, $actor);
        } else {
            $this->notifications->facultyCreated($user, $actor);
        }

        $sendEmail = (bool) ($data['send_enrollment_email'] ?? true);
        $delivery = $sendEmail
            ? $this->enrollmentLinks->sendToUser($user)
            : [
                'url' => $this->enrollmentLinks->createSignedUrl($user),
                'email_sent' => false,
                'email_error' => null,
                'recipient' => $user->email,
            ];

        return [
            'user' => $user,
            'enrollment_url' => $delivery['url'],
            'email_sent' => $delivery['email_sent'],
            'email_error' => $delivery['email_error'],
        ];
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     staff_role_id?: int|null
     * }  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        if (! in_array($user->role, [UserRole::Admin, UserRole::Faculty], true)) {
            throw ValidationException::withMessages([
                'role' => ['Only Administrator and Faculty accounts can be updated here.'],
            ]);
        }

        $email = strtolower(trim($data['email']));
        $name = trim($data['name']);

        if (User::query()->where('email', $email)->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email is already in use.'],
            ]);
        }

        $staffRoleId = $user->staff_role_id;

        if ($user->role === UserRole::Admin) {
            $staffRoleId = null;
            if (! empty($data['staff_role_id'])) {
                $staffRoleId = StaffRole::query()
                    ->whereKey($data['staff_role_id'])
                    ->where('slug', '!=', 'chief_super_admin')
                    ->value('id');
            }
        }

        $user->fill([
            'name' => $name,
            'email' => $email,
            'staff_role_id' => $user->role === UserRole::Admin ? $staffRoleId : null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->role === UserRole::Admin) {
            $this->notifications->administratorUpdated($user, $actor);
        }

        return $user->fresh();
    }

    public function suggestedAccountId(UserRole $role): string
    {
        $prefix = $role === UserRole::Faculty ? 'FACULTY-' : 'ADMIN-';

        do {
            $candidate = $prefix.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (User::query()->where('account_id', $candidate)->exists());

        return $candidate;
    }
}
