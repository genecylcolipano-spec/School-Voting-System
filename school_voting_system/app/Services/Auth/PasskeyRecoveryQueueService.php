<?php

namespace App\Services\Auth;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PasskeyRecoveryQueueService
{
    public const MATCH_EXACT = 'exact';

    public const MATCH_EMAIL_MISMATCH = 'email_mismatch';

    public const MATCH_UNKNOWN = 'unknown';

    public function __construct(
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
        protected AuditLogService $audit,
        protected PortalNotificationService $notifications,
    ) {}

    public function recordUnmatchedAttempt(
        string $accountId,
        string $email,
        ?string $ip = null,
        ?string $userAgent = null,
    ): PasskeyRecoveryRequest {
        $accountId = trim($accountId);
        $email = strtolower(trim($email));

        $existing = PasskeyRecoveryRequest::query()
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->where('user_id', null)
            ->where('account_id', $accountId)
            ->where('email', $email)
            ->first();

        if ($existing) {
            $existing->forceFill([
                'requested_ip' => $ip,
                'requested_user_agent' => $userAgent,
            ])->save();

            return $existing->fresh();
        }

        return PasskeyRecoveryRequest::query()->create([
            'user_id' => null,
            'account_id' => $accountId,
            'email' => $email,
            'status' => PasskeyRecoveryRequest::STATUS_PENDING,
            'requested_ip' => $ip,
            'requested_user_agent' => $userAgent,
        ]);
    }

    /**
     * @return Collection<int, PasskeyRecoveryRequest>
     */
    public function pendingQueue(?User $actor = null): Collection
    {
        $actor ??= auth()->user();

        $this->collapsePendingUnmatchedDuplicates();

        $requests = PasskeyRecoveryRequest::query()
            ->with('user')
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $usersByAccount = User::query()
            ->whereIn('account_id', $requests->pluck('account_id')->unique()->filter()->all())
            ->get()
            ->keyBy('account_id');

        return $requests->map(fn (PasskeyRecoveryRequest $request) => $this->present(
            $request,
            $usersByAccount,
            $actor,
        ));
    }

    /**
     * @param  Collection<string, User>  $usersByAccount
     */
    public function present(
        PasskeyRecoveryRequest $request,
        Collection $usersByAccount,
        ?User $actor = null,
    ): PasskeyRecoveryRequest {
        $user = $request->user ?: $usersByAccount->get($request->account_id);
        $match = $this->matchState($request, $user);
        $onFileEmail = $user?->email;
        $canIssue = $user !== null && $actor !== null && $actor->can('issuePasskeyReset', $user);
        $canDismiss = $actor?->isSuperAdmin() ?? false;

        $confirm = null;
        if ($user && $onFileEmail) {
            $confirm = $match === self::MATCH_EMAIL_MISMATCH
                ? "Send an enrollment link to the email on file ({$onFileEmail}) for {$user->name}? The requested email was {$request->email}."
                : "Send an enrollment link to {$onFileEmail} for {$user->name}?";
        }

        $request->setAttribute('queue_match', $match);
        $request->setAttribute('queue_user', $user);
        $request->setAttribute('queue_user_url', $user ? $this->accountUrl($user) : null);
        $request->setAttribute('queue_on_file_email', $onFileEmail);
        $request->setAttribute('queue_can_issue', $canIssue);
        $request->setAttribute('queue_can_dismiss', $canDismiss);
        $request->setAttribute('queue_confirm', $confirm);
        $request->setAttribute('queue_enroll_url', route('admin.recovery.enroll', $request));
        $request->setAttribute('queue_dismiss_url', route('admin.recovery.dismiss', $request));

        return $request;
    }

    public function findAccount(PasskeyRecoveryRequest $request): ?User
    {
        if ($request->user_id) {
            $linked = $request->user ?: User::query()->find($request->user_id);
            if ($linked) {
                return $linked;
            }
        }

        return User::findByAccountId((string) $request->account_id);
    }

    /**
     * @return array{url: string, email_sent: bool, email_error: string|null, recipient: string|null, expires_in_minutes: int}
     */
    public function issueEnrollment(PasskeyRecoveryRequest $request, User $actor): array
    {
        if ($request->status !== PasskeyRecoveryRequest::STATUS_PENDING) {
            throw new HttpException(422, 'This request is no longer pending.');
        }

        $user = $this->findAccount($request);
        if (! $user) {
            throw new HttpException(422, 'No portal account matches this Account ID.');
        }

        if ($actor->cannot('issuePasskeyReset', $user)) {
            throw new HttpException(403, 'You cannot issue a passkey reset for this account.');
        }

        $expiresInMinutes = max(60, (int) config('enrollment.link_expiration_hours', 24) * 60);
        $result = $this->enrollmentLinks->sendToUser($user, $user->email, $expiresInMinutes, $actor);

        $request->forceFill([
            'user_id' => $user->id,
            'status' => PasskeyRecoveryRequest::STATUS_RESOLVED,
            'resolved_by' => $actor->id,
            'resolved_at' => now(),
            'last_sent_at' => $result['email_sent'] ? now() : $request->last_sent_at,
        ])->save();

        $this->audit->record(
            $actor,
            "Issued passkey enrollment link for {$user->account_id}",
            AuditActionType::Passkey,
            targetType: 'user',
            targetId: $user->id,
            metadata: [
                'recovery_request_id' => $request->id,
                'email_sent' => $result['email_sent'],
                'requested_email' => $request->email,
                'recipient' => $result['recipient'],
            ],
        );

        $this->notifications->passkeyResetCompleted($user, $actor);

        return [
            'user' => $user,
            'url' => $result['url'],
            'email_sent' => $result['email_sent'],
            'email_error' => $result['email_error'],
            'recipient' => $result['recipient'],
            'expires_in_minutes' => $expiresInMinutes,
        ];
    }

    public function dismiss(PasskeyRecoveryRequest $request, User $actor): PasskeyRecoveryRequest
    {
        if (! $actor->isSuperAdmin()) {
            throw new HttpException(403, 'Only a super administrator can dismiss recovery requests.');
        }

        if ($request->status !== PasskeyRecoveryRequest::STATUS_PENDING) {
            throw new HttpException(422, 'This request is no longer pending.');
        }

        $request->forceFill([
            'status' => PasskeyRecoveryRequest::STATUS_DISMISSED,
            'resolved_by' => $actor->id,
            'resolved_at' => now(),
        ])->save();

        $this->audit->record(
            $actor,
            "Dismissed passkey recovery request for {$request->account_id}",
            AuditActionType::Passkey,
            targetType: 'passkey_recovery_request',
            targetId: $request->id,
            metadata: [
                'account_id' => $request->account_id,
                'email' => $request->email,
            ],
        );

        return $request->fresh();
    }

    public function accountUrl(User $user): string
    {
        return match ($user->role) {
            UserRole::Student => route('admin.students.show', $user),
            UserRole::Admin => route('super-admin.administrators.show', $user),
            UserRole::Faculty => route('super-admin.faculty.show', $user),
            default => route('super-admin.dashboard', ['portal_q' => $user->account_id]),
        };
    }

    public function matchState(PasskeyRecoveryRequest $request, ?User $user): string
    {
        if (! $user) {
            return self::MATCH_UNKNOWN;
        }

        $requested = strtolower(trim((string) $request->email));
        $onFile = strtolower(trim((string) $user->email));

        return $requested !== '' && $requested === $onFile
            ? self::MATCH_EXACT
            : self::MATCH_EMAIL_MISMATCH;
    }

    protected function collapsePendingUnmatchedDuplicates(): void
    {
        $groups = PasskeyRecoveryRequest::query()
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->whereNull('user_id')
            ->orderByDesc('id')
            ->get(['id', 'account_id', 'email'])
            ->groupBy(fn (PasskeyRecoveryRequest $row) => strtolower((string) $row->account_id).'|'.strtolower((string) $row->email));

        $extraIds = [];
        foreach ($groups as $rows) {
            if ($rows->count() < 2) {
                continue;
            }

            $extraIds = array_merge($extraIds, $rows->skip(1)->pluck('id')->all());
        }

        if ($extraIds === []) {
            return;
        }

        PasskeyRecoveryRequest::query()
            ->whereIn('id', $extraIds)
            ->update([
                'status' => PasskeyRecoveryRequest::STATUS_DISMISSED,
                'resolved_at' => now(),
            ]);
    }
}
