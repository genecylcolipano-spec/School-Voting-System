<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use App\Models\Passkey;
use App\Models\User;
use App\Services\Media\ImageCompressionService;
use App\Support\AdminPortal;
use App\Support\UserAgentParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile / settings form.
     */
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load(['staffRole'])->loadCount(['passkeys', 'judgingAssignments']);

        $currentPasskeyId = (int) $request->session()->get('authenticated_passkey_id', 0);

        $passkeys = $user->passkeys()
            ->latest('created_at')
            ->get(['id', 'device_name', 'name', 'counter', 'last_used_at', 'created_at', 'status'])
            ->map(function (Passkey $passkey) use ($currentPasskeyId) {
                $label = $passkey->device_name ?? $passkey->name ?? 'Device';

                $passkey->setAttribute('display_name', $label);
                $passkey->setAttribute('device_type', UserAgentParser::deviceTypeFromName($label));
                $passkey->setAttribute('browser', UserAgentParser::browserFromDeviceName($label));
                $passkey->setAttribute('operating_system', UserAgentParser::platformFromDeviceName($label));
                $passkey->setAttribute('is_current', $currentPasskeyId > 0 && (int) $passkey->id === $currentPasskeyId);

                return $passkey;
            });

        if ($currentPasskeyId === 0 && $passkeys->isNotEmpty()) {
            $recent = $passkeys->sortByDesc(fn (Passkey $p) => $p->last_used_at?->getTimestamp() ?? 0)->first();
            if ($recent) {
                $recent->setAttribute('is_current', true);
            }
        }

        $activeSessions = $this->activeSessionsFor($user->id, (string) $request->session()->getId());
        $loginHistoryLimit = ($user->isAdmin() || $user->isSuperAdmin()) ? 10 : 15;
        $loginHistory = $this->loginHistoryFor($user->id, $loginHistoryLimit);
        $lastLogin = $loginHistory[0] ?? null;
        $currentSession = collect($activeSessions)->firstWhere('is_current', true);

        $settingsData = [
            'user' => $user,
            'section' => $this->resolveSection($request->query('section')),
            'passkeys' => $passkeys,
            'activeSessions' => $activeSessions,
            'loginHistory' => $loginHistory,
            'systemAccessHistory' => $user->isSuperAdmin()
                ? $this->systemAccessHistoryFor($user->id)
                : [],
            'passwordlessEnabled' => $user->passkeys_count > 0,
            'trustedDeviceCount' => $passkeys->filter(fn (Passkey $p) => $p->last_used_at !== null)->count(),
            'lastLogin' => $lastLogin,
            'lastAuthentication' => $passkeys
                ->filter(fn (Passkey $p) => $p->last_used_at !== null)
                ->sortByDesc(fn (Passkey $p) => $p->last_used_at?->getTimestamp() ?? 0)
                ->first(),
            'securityContext' => [
                'ip' => $lastLogin['ip_address'] ?? $currentSession['ip_address'] ?? $request->ip(),
                'browser' => $lastLogin['browser'] ?? $currentSession['browser'] ?? UserAgentParser::parse($request->userAgent())['browser'],
                'os' => $lastLogin['os'] ?? $currentSession['os'] ?? UserAgentParser::parse($request->userAgent())['os'],
                'at' => $lastLogin['occurred_at'] ?? $currentSession['last_activity'] ?? null,
            ],
            'accountStatus' => filled($user->archived_at ?? null)
                ? 'Archived'
                : ($user->is_active ? 'Active' : 'Inactive'),
            'departmentLabel' => $user->staffRole?->name ?? ($user->isSuperAdmin() ? 'System Administration' : '—'),
        ];

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return view('profile.edit-admin', array_merge(
                AdminPortal::layoutData($request),
                $settingsData,
            ));
        }

        $settingsData['notificationsCount'] = AdminPortal::notificationCount($user);
        $settingsData['portalComponent'] = $user->isFaculty() ? 'faculty-portal' : 'student-portal';

        return view('profile.edit-student', $settingsData);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->has('phone') && ($user->isStudent() || $user->isFaculty())) {
            $user->phone = $validated['phone'] ?? null;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $this->storeAvatar($request->file('avatar'));
        }

        $user->save();
        $request->user()->refresh();

        return Redirect::route('profile.edit', ['section' => 'profile'])
            ->with('status', 'profile-updated')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Sign out every other active session for the current user.
     */
    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return Redirect::route('profile.edit', ['section' => 'security'])
            ->with('status', 'other-sessions-logged-out');
    }

    /**
     * Delete the user's account (students / faculty only; passwordless confirmation).
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->isStudent() || $user->isFaculty(), 403);

        $request->validateWithBag('userDeletion', [
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected function resolveSection(mixed $section): string
    {
        $value = is_string($section) ? $section : 'profile';

        return in_array($value, ['profile', 'devices', 'security'], true) ? $value : 'profile';
    }

    protected function storeAvatar(UploadedFile $file): string
    {
        return app(ImageCompressionService::class)->storeSquareAvatar(
            $file,
            'avatars',
            ImageCompressionService::AVATAR_SIZE,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function activeSessionsFor(int $userId, string $currentSessionId): array
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $parsed = UserAgentParser::parse($session->user_agent);

                return [
                    'id' => $session->id,
                    'is_current' => $session->id === $currentSessionId,
                    'ip_address' => $session->ip_address,
                    'browser' => $parsed['browser'],
                    'os' => $parsed['os'],
                    'device' => $parsed['device'],
                    'last_activity' => $session->last_activity
                        ? \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)
                        : null,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function loginHistoryFor(int $userId, int $limit = 15): array
    {
        return AuditLog::query()
            ->where('user_id', $userId)
            ->where('action_type', AuditActionType::Auth)
            ->where('action', 'like', '%login%')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (AuditLog $log) {
                $parsed = UserAgentParser::parse($log->user_agent);

                return [
                    'occurred_at' => $log->created_at,
                    'browser' => $parsed['browser'],
                    'os' => $parsed['os'],
                    'device' => $parsed['device'],
                    'ip_address' => $log->ip_address,
                    'status' => $log->status === 'success' ? 'Successful' : ucfirst((string) $log->status),
                ];
            })
            ->all();
    }

    /**
     * Broader activity trail for Super Admin security overview.
     *
     * @return list<array<string, mixed>>
     */
    protected function systemAccessHistoryFor(int $userId): array
    {
        return AuditLog::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (AuditLog $log) {
                $parsed = UserAgentParser::parse($log->user_agent);

                return [
                    'occurred_at' => $log->created_at,
                    'action' => $log->action,
                    'type' => $log->action_type instanceof AuditActionType
                        ? $log->action_type->value
                        : (string) $log->action_type,
                    'browser' => $parsed['browser'],
                    'os' => $parsed['os'],
                    'ip_address' => $log->ip_address,
                    'status' => $log->status === 'success' ? 'Successful' : ucfirst((string) $log->status),
                ];
            })
            ->all();
    }
}
