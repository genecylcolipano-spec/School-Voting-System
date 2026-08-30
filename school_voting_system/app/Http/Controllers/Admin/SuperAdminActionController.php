<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\PasskeyStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\BulkUsersRequest;
use App\Http\Requests\Admin\SuperAdmin\ElectionActionRequest;
use App\Http\Requests\Admin\SuperAdmin\GenerateReportRequest;
use App\Http\Requests\Admin\SuperAdmin\PasskeyActionRequest;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use App\Services\Admin\ElectionResultsPublishingService;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\SuperAdmin\ComplianceReportService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SuperAdminActionController extends Controller
{
    public function __construct(
        protected AuditLogService $audit,
        protected ElectionLifecycleService $elections,
        protected ElectionResultsPublishingService $electionPublishing,
        protected PortalNotificationService $notifications,
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
        protected AdminScopeService $scope,
        protected ComplianceReportService $complianceReports,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString();

        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $term = '%'.$q.'%';

        $users = User::query()
            ->where(fn ($query) => $query
                ->where('account_id', 'like', $term)
                ->orWhere('name', 'like', $term)
                ->orWhere('email', 'like', $term))
            ->limit(8)
            ->get(['id', 'account_id', 'name', 'role']);

        $elections = Election::query()
            ->where('title', 'like', $term)
            ->limit(5)
            ->get(['id', 'title', 'status', 'slug']);

        return response()->json([
            'results' => [
                'accounts' => $users->map(fn (User $user) => [
                    'id' => $user->id,
                    'account_id' => $user->account_id,
                    'name' => $user->name,
                    'role' => $user->role?->value,
                    'url' => $this->accountSearchUrl($user),
                ])->all(),
                'elections' => $elections->map(fn (Election $election) => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $election->status?->value,
                    'url' => route('admin.elections.edit', $election),
                ])->all(),
            ],
        ]);
    }

    protected function accountSearchUrl(User $user): string
    {
        return match ($user->role) {
            UserRole::Student => route('admin.students.show', $user),
            UserRole::Admin => route('super-admin.administrators.show', $user),
            UserRole::Faculty => route('super-admin.faculty.show', $user),
            default => route('super-admin.dashboard', ['portal_q' => $user->account_id]),
        };
    }

    public function bulkUsers(BulkUsersRequest $request): RedirectResponse|StreamedResponse
    {
        $validated = $request->validated();
        $action = $validated['action'];

        $users = User::query()
            ->whereIn('id', $validated['user_ids'])
            ->with('staffRole')
            ->withCount('passkeys')
            ->get();

        $actor = $request->user();

        if ($action === 'export') {
            $this->audit->record(
                $actor,
                'Exported portal accounts (CSV): '.count($validated['user_ids']).' selected',
                AuditActionType::Report,
                metadata: ['ids' => $validated['user_ids']],
            );

            return $this->exportPortalUsers($users);
        }

        $activated = 0;
        $deactivated = 0;
        $deleted = 0;
        $skippedDelete = 0;
        $emailsSent = 0;
        $emailFailures = [];
        $manualLinks = [];

        foreach ($users as $user) {
            $isPortalAdmin = in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true);
            $adminName = $user->name;
            $adminAccountId = $user->account_id;

            match ($action) {
                'activate' => tap($user->forceFill(['is_active' => true])->save(), function () use ($isPortalAdmin, $user, $actor, &$activated) {
                    $activated++;
                    if ($isPortalAdmin) {
                        $this->notifications->administratorUpdated($user->fresh(), $actor);
                    }
                }),
                'deactivate' => tap($user->forceFill(['is_active' => false])->save(), function () use ($isPortalAdmin, $user, $actor, &$deactivated) {
                    $deactivated++;
                    if ($isPortalAdmin) {
                        $this->notifications->administratorUpdated($user->fresh(), $actor);
                    }
                }),
                'delete' => $this->deletePortalUser($user, $actor, $adminName, $adminAccountId, $isPortalAdmin, $deleted, $skippedDelete),
                'resend_access' => $this->resendPortalAccess($user, $emailsSent, $emailFailures, $manualLinks),
                default => null,
            };
        }

        $this->audit->record(
            $actor,
            'Bulk user action: '.$action.' on '.count($validated['user_ids']).' accounts',
            AuditActionType::User,
            metadata: ['ids' => $validated['user_ids']],
        );

        $redirect = back();

        return match ($action) {
            'activate' => $redirect->with('success', "{$activated} account(s) activated."),
            'deactivate' => $redirect->with('success', "{$deactivated} account(s) deactivated. Deactivated accounts cannot sign in."),
            'delete' => $redirect->with('success', $this->deleteSummaryMessage($deleted, $skippedDelete)),
            'resend_access' => $this->resendAccessRedirect($redirect, $emailsSent, $emailFailures, $manualLinks),
            default => $redirect->with('success', 'Bulk action applied successfully.'),
        };
    }

    protected function deletePortalUser(
        User $user,
        User $actor,
        string $adminName,
        ?string $adminAccountId,
        bool $isPortalAdmin,
        int &$deleted,
        int &$skippedDelete,
    ): void {
        if ($user->id === $actor->id || $user->isSuperAdmin()) {
            $skippedDelete++;

            return;
        }

        $user->delete();
        $deleted++;

        if ($isPortalAdmin) {
            $this->notifications->administratorDeleted($adminName, $adminAccountId, $actor);
        }
    }

    /**
     * @param  list<string>  $emailFailures
     * @param  list<array{account_id: string|null, url: string}>  $manualLinks
     */
    protected function resendPortalAccess(User $user, int &$emailsSent, array &$emailFailures, array &$manualLinks): void
    {
        $result = $this->enrollmentLinks->sendToUser($user);

        if ($result['email_sent']) {
            $emailsSent++;

            return;
        }

        $reason = $result['email_error'] ?? 'Email delivery failed.';
        $emailFailures[] = ($user->account_id ?? 'Unknown').': '.$reason;

        if ($result['url']) {
            $manualLinks[] = [
                'account_id' => $user->account_id,
                'url' => $result['url'],
            ];
        }
    }

    /**
     * @param  list<string>  $emailFailures
     * @param  list<array{account_id: string|null, url: string}>  $manualLinks
     */
    protected function resendAccessRedirect(RedirectResponse $redirect, int $emailsSent, array $emailFailures, array $manualLinks): RedirectResponse
    {
        $message = "{$emailsSent} enrollment email(s) sent.";

        if ($emailFailures !== []) {
            $message .= ' '.count($emailFailures).' account(s) need manual follow-up.';
        }

        $redirect = $redirect->with('success', $message);

        if ($emailFailures !== []) {
            $redirect = $redirect->with('warning', implode(' ', $emailFailures));
        }

        if ($manualLinks !== []) {
            $redirect = $redirect->with('enrollment_links', $manualLinks);
        }

        return $redirect;
    }

    protected function deleteSummaryMessage(int $deleted, int $skippedDelete): string
    {
        $parts = [];

        if ($deleted > 0) {
            $parts[] = "{$deleted} account(s) deleted";
        }

        if ($skippedDelete > 0) {
            $parts[] = "{$skippedDelete} account(s) skipped (Super Admin or your own account)";
        }

        return $parts !== [] ? implode('. ').'.' : 'No accounts were deleted.';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    protected function exportPortalUsers($users): StreamedResponse
    {
        $filename = 'portal-accounts-'.now()->format('Y-m-d-His').'.csv';

        return response()->stream(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'account_id',
                'name',
                'email',
                'role',
                'staff_role',
                'passkeys',
                'is_active',
                'student_status',
                'grade_level',
                'section',
            ]);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->account_id,
                    $user->name,
                    $user->email,
                    $user->role?->value,
                    $user->staffRole?->name,
                    $user->passkeys_count ?? $user->passkeys()->count(),
                    $user->is_active ? '1' : '0',
                    $user->student_status?->value,
                    $user->grade_level,
                    $user->section,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function electionAction(ElectionActionRequest $request, Election $election): RedirectResponse
    {
        $validated = $request->validated();

        $actor = $request->user();
        $action = $validated['action'];

        if ($action === 'schedule' && ! $this->elections->canSchedule($election)) {
            return back()->with('error', 'This election cannot be scheduled.');
        }

        if ($action !== 'schedule' && ! array_key_exists($action, $this->elections->availableActions($election))) {
            return back()->with('error', 'That action is not available for this election.');
        }

        try {
            match ($action) {
                'open' => $this->elections->open($election, $actor),
                'pause' => $this->elections->pause($election, $actor),
                'resume' => $this->elections->resume($election, $actor),
                'close' => $this->elections->close($election, $actor),
                'annul' => $this->elections->annul($election, $actor),
                'rerun' => $this->elections->rerun($election, $actor),
                'lock' => $this->elections->lockResults($election, $actor, true),
                'unlock' => $this->elections->lockResults($election, $actor, false),
                'schedule' => $this->elections->schedule(
                    $election,
                    $actor,
                    $validated['scheduled_open_at'] ?? null,
                    $validated['scheduled_close_at'] ?? null,
                ),
                'publish_results' => $this->electionPublishing->publish($election, $actor),
                'unpublish_results' => $this->electionPublishing->unpublish($election, $actor),
                default => null,
            };
        } catch (HttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $messages = [
            'open' => 'Election opened.',
            'pause' => 'Election paused.',
            'resume' => 'Election resumed.',
            'close' => 'Election closed.',
            'annul' => 'Election annulled.',
            'rerun' => 'Election re-run created as a draft.',
            'lock' => 'Election results locked.',
            'unlock' => 'Election results unlocked.',
            'schedule' => 'Election schedule saved.',
            'publish_results' => 'Official election results published.',
            'unpublish_results' => 'Official election results unpublished.',
        ];

        return back()->with('success', $messages[$action] ?? 'Election action completed.');
    }

    public function passkeyAction(PasskeyActionRequest $request, Passkey $passkey): RedirectResponse
    {
        $validated = $request->validated();
        $action = $validated['action'];
        $actor = $request->user();
        $passkey->loadMissing('user');
        $owner = $passkey->user;

        if (! $passkey->isUsable()) {
            return back()->with('error', 'This passkey is already disabled.');
        }

        if (
            $owner
            && (int) $passkey->user_id === (int) $actor->id
            && Passkey::remainingUsableCountFor($actor, (int) $passkey->id) === 0
        ) {
            return back()->with('error', 'You cannot disable your only remaining passkey. Register another device in Settings first.');
        }

        if ($action === 'revoke') {
            $passkey->forceFill([
                'status' => PasskeyStatus::Revoked,
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
            ])->save();
        } else {
            $passkey->forceFill([
                'status' => PasskeyStatus::Lost,
                'marked_lost_at' => now(),
            ])->save();
        }

        $accountId = $owner?->account_id ?? '#'.$passkey->id;
        $verb = $action === 'lost' ? 'Marked lost' : 'Revoked';

        $this->audit->record(
            $actor,
            "{$verb} passkey for {$accountId}",
            AuditActionType::Passkey,
            targetType: 'passkey',
            targetId: $passkey->id,
        );

        $pendingRecovery = $owner && PasskeyRecoveryRequest::query()
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->where(function ($query) use ($owner) {
                $query->where('user_id', $owner->id)
                    ->orWhere('account_id', $owner->account_id);
            })
            ->exists();

        $redirect = back()->with('success', $verb.' passkey for '.$accountId.'. This device can no longer sign in.');

        if ($owner) {
            $redirect->with('disabled_passkey', [
                'user_id' => $owner->id,
                'name' => $owner->name,
                'account_id' => $owner->account_id,
                'action' => $action,
                'has_pending_recovery' => $pendingRecovery,
                'devices_url' => $owner->adminDevicesUrl($actor),
            ]);
        }

        return $redirect;
    }

    public function exportAuditLogs(Request $request): Response
    {
        $csv = $this->audit->exportCsv([
            'search' => $request->string('search')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
            'module' => $request->string('module')->toString() ?: $request->string('action_type')->toString() ?: null,
            'role' => $request->string('role')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
        ]);

        $this->audit->record($request->user(), 'Exported audit logs (CSV)', AuditActionType::Report);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-trail-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function generateReport(GenerateReportRequest $request): Response
    {
        $type = $request->validated('report');
        $electionId = $request->integer('election_id') ?: null;
        $format = $request->validated('format') ?: 'html';

        $this->audit->record(
            $request->user(),
            'Generated report: '.$type.($format === 'pdf' ? ' (pdf)' : ''),
            AuditActionType::Report,
        );

        if ($format === 'pdf') {
            return $this->complianceReports->pdf($type, $request->user(), $electionId);
        }

        $content = $this->complianceReports->html($type, $request->user(), $electionId);
        $filename = $type.'-'.now()->format('Y-m-d').'.html';

        return response($content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
