<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\PasskeyStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\BulkUsersRequest;
use App\Http\Requests\Admin\SuperAdmin\CreateBackupRequest;
use App\Http\Requests\Admin\SuperAdmin\ElectionActionRequest;
use App\Http\Requests\Admin\SuperAdmin\GenerateReportRequest;
use App\Http\Requests\Admin\SuperAdmin\PasskeyActionRequest;
use App\Http\Requests\Admin\SuperAdmin\UpdateSystemSettingsRequest;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Admin\ElectionResultsPublishingService;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\SuperAdmin\BackupService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use App\Services\SuperAdmin\SuperAdminDashboardService;
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
        protected BackupService $backups,
        protected SuperAdminDashboardService $dashboard,
        protected PortalNotificationService $notifications,
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
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
            ->get(['id', 'title', 'status']);

        return response()->json([
            'results' => [
                'accounts' => $users,
                'elections' => $elections,
            ],
        ]);
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

        try {
            match ($validated['action']) {
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

        if (in_array($validated['action'], ['publish_results', 'unpublish_results'], true)) {
            return back()->with('success', $validated['action'] === 'publish_results'
                ? 'Official election results published.'
                : 'Official election results unpublished.');
        }

        return back()->with('success', 'Election action completed.');
    }

    public function passkeyAction(PasskeyActionRequest $request, Passkey $passkey): RedirectResponse
    {
        $validated = $request->validated();

        $actor = $request->user();

        match ($validated['action']) {
            'revoke' => $passkey->forceFill([
                'status' => PasskeyStatus::Revoked,
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
            ])->save(),
            'reassign' => $passkey->forceFill([
                'reassigned_to_user_id' => $validated['reassigned_to_user_id'],
            ])->save(),
            'expiry' => $passkey->forceFill([
                'expires_at' => $validated['expires_at'],
            ])->save(),
            'lost' => $passkey->forceFill([
                'status' => PasskeyStatus::Lost,
                'marked_lost_at' => now(),
            ])->save(),
            default => null,
        };

        $this->audit->record(
            $actor,
            "Passkey {$validated['action']}: #{$passkey->id}",
            AuditActionType::Passkey,
            targetType: 'passkey',
            targetId: $passkey->id,
        );

        return back()->with('success', 'Passkey updated.');
    }

    public function updateSettings(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['ip_whitelist_enabled'] = $request->boolean('ip_whitelist_enabled');
        $validated['two_factor_recovery_enabled'] = $request->boolean('two_factor_recovery_enabled');
        $validated['public_results_published'] = $request->boolean('public_results_published');

        app(\App\Services\SuperAdmin\SystemSettingsService::class)->update($validated);

        $this->audit->record($request->user(), 'Updated system security settings', AuditActionType::Security);
        $this->notifications->systemSettingsUpdated($request->user());

        return back()->with('success', 'Security settings saved.');
    }

    public function createBackup(CreateBackupRequest $request): RedirectResponse
    {
        $type = $request->validated('type') ?: \App\Services\SuperAdmin\BackupService::TYPE_FULL;

        $this->backups->create($request->user(), $type);

        return back()->with('success', 'Backup created successfully.');
    }

    public function downloadBackup(Request $request, SystemBackup $backup): StreamedResponse
    {
        $this->audit->record(
            $request->user(),
            'Downloaded Backup',
            AuditActionType::Backup,
            targetType: 'backup',
            targetId: $backup->id,
            metadata: ['label' => $backup->label],
        );

        return $this->backups->download($backup);
    }

    public function exportAuditLogs(Request $request): Response
    {
        $query = AuditLog::query()->latest();

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->string('action_type'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $logs = $query->limit(5000)->get();

        $csv = "Timestamp,Admin,Role,Action,Type,IP,Device,Status\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                '"'.$log->created_at?->toDateTimeString().'"',
                '"'.str_replace('"', '""', $log->admin_name).'"',
                '"'.str_replace('"', '""', $log->admin_role ?? '').'"',
                '"'.str_replace('"', '""', $log->action).'"',
                '"'.$log->action_type?->value.'"',
                '"'.($log->ip_address ?? '').'"',
                '"'.str_replace('"', '""', $log->device_name ?? '').'"',
                '"'.$log->status.'"',
            ])."\n";
        }

        $this->audit->record($request->user(), 'Exported audit logs (CSV)', AuditActionType::Report);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-trail-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function generateReport(GenerateReportRequest $request): Response
    {
        $validated = $request->validated();

        $content = match ($validated['report']) {
            'election_summary' => $this->electionSummaryReport(),
            'voter_turnout' => $this->voterTurnoutReport(),
            'audit_trail' => $this->auditTrailReport(),
            'passkey_inventory' => $this->passkeyInventoryReport(),
        };

        $this->audit->record($request->user(), 'Generated report: '.$validated['report'], AuditActionType::Report);

        return response($content, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="'.$validated['report'].'-'.now()->format('Y-m-d').'.html"',
        ]);
    }

    protected function electionSummaryReport(): string
    {
        $elections = Election::query()->withCount('votes')->get();
        $rows = $elections->map(fn ($e) => "<tr><td>{$e->title}</td><td>{$e->status?->value}</td><td>{$e->votes_count}</td><td>{$e->integrity_hash}</td></tr>")->join('');

        return $this->reportShell('Election Summary Report', "<table border='1' cellpadding='8'><tr><th>Election</th><th>Status</th><th>Votes</th><th>Integrity Hash</th></tr>{$rows}</table>");
    }

    protected function voterTurnoutReport(): string
    {
        $stats = $this->dashboard->statistics();

        return $this->reportShell('Voter Turnout Report', "
            <p>Eligible Students: {$stats['eligible_students']}</p>
            <p>Students Voted: {$stats['voted_students']}</p>
            <p>Turnout: {$stats['voter_turnout']}%</p>
        ");
    }

    protected function auditTrailReport(): string
    {
        $logs = AuditLog::query()->latest()->limit(100)->get();
        $rows = $logs->map(fn ($l) => "<tr><td>{$l->created_at}</td><td>{$l->admin_name}</td><td>{$l->action}</td><td>{$l->status}</td></tr>")->join('');

        return $this->reportShell('Audit Trail Report', "<table border='1' cellpadding='8'><tr><th>Time</th><th>Admin</th><th>Action</th><th>Status</th></tr>{$rows}</table>");
    }

    protected function passkeyInventoryReport(): string
    {
        $passkeys = Passkey::query()->with('user')->get();
        $rows = $passkeys->map(fn ($p) => "<tr><td>{$p->user?->name}</td><td>{$p->credential_id}</td><td>{$p->device_name}</td><td>{$p->status?->value}</td><td>{$p->last_used_at}</td></tr>")->join('');

        return $this->reportShell('Passkey Inventory', "<table border='1' cellpadding='8'><tr><th>Account</th><th>Credential ID</th><th>Device</th><th>Status</th><th>Last Used</th></tr>{$rows}</table>");
    }

    protected function reportShell(string $title, string $body): string
    {
        $school = config('app.name');
        $timestamp = now()->toDayDateTimeString();
        $signatory = auth()->user()?->name ?? 'Chief Super Admin';

        return "<!DOCTYPE html><html><head><meta charset='utf-8'><title>{$title}</title>
            <style>body{font-family:Georgia,serif;margin:40px;color:#111}header{border-bottom:3px solid #4c1d95;padding-bottom:16px}footer{margin-top:40px;font-size:12px;color:#555}</style>
            </head><body>
            <header><h1>{$school}</h1><h2>{$title}</h2><p>Generated: {$timestamp}</p></header>
            {$body}
            <footer><p>Digitally signed by: <strong>{$signatory}</strong></p><p>This document is system-generated and verifiable against audit logs.</p></footer>
            </body></html>";
    }
}
