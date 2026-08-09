<?php

namespace App\Http\Controllers\Admin\System;

use App\Enums\AuditActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\CreateBackupRequest;
use App\Models\SystemBackup;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\SuperAdmin\BackupService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemBackupController extends Controller
{
    public function __construct(
        protected BackupService $backups,
        protected AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = $request->string('search')->toString() ?: null;
        $type = $request->string('type')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;

        $stats = $this->backups->dashboardStats();

        $backups = SystemBackup::query()
            ->with('creator:id,name,account_id')
            ->when($search, function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('label', 'like', $term)
                        ->orWhere('type', 'like', $term)
                        ->orWhereHas('creator', fn ($q) => $q->where('name', 'like', $term)->orWhere('account_id', 'like', $term));
                });
            })
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('completed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('completed_at', '<=', $to))
            ->latest('completed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.system.backups', array_merge(AdminPortal::layoutData($request), [
            'stats' => $stats,
            'storageUsed' => $this->backups->formatBytes($stats['storage_bytes']),
            'backups' => $backups,
            'filters' => [
                'search' => $search ?? '',
                'type' => $type ?? '',
                'status' => $status ?? '',
                'from' => $from ?? '',
                'to' => $to ?? '',
            ],
            'backupTypes' => [
                BackupService::TYPE_FULL => 'Full System',
                BackupService::TYPE_ELECTION_RESULTS => 'Election Results',
                BackupService::TYPE_STUDENT_DATA => 'Student Data',
                BackupService::TYPE_ADMIN_ACCOUNTS => 'Admin Accounts',
            ],
        ]));
    }

    public function show(Request $request, SystemBackup $backup): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $backup->load('creator:id,name,account_id,role');

        return view('admin.system.backup-show', array_merge(AdminPortal::layoutData($request), [
            'backup' => $backup,
            'details' => $this->backups->details($backup),
        ]));
    }

    public function store(CreateBackupRequest $request): RedirectResponse
    {
        $type = $request->validated('type') ?: BackupService::TYPE_FULL;

        $this->backups->create($request->user(), $type);

        return redirect()
            ->route('super-admin.system.backups.index')
            ->with('success', 'Backup created successfully.');
    }

    public function download(Request $request, SystemBackup $backup): StreamedResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

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

    public function destroy(Request $request, SystemBackup $backup): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $this->backups->delete($request->user(), $backup);

        return redirect()
            ->route('super-admin.system.backups.index')
            ->with('success', 'Backup deleted.');
    }
}
