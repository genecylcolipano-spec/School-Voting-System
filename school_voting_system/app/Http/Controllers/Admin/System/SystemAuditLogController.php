<?php

namespace App\Http\Controllers\Admin\System;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SuperAdmin\AuditLogService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAuditLogController extends Controller
{
    public function __construct(protected AuditLogService $audit) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = $request->string('search')->toString() ?: null;
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;
        $module = $request->string('module')->toString() ?: null;
        $role = $request->string('role')->toString() ?: null;
        $userId = $request->integer('user_id') ?: null;

        $logs = $this->audit->filteredQuery([
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'module' => $module,
            'role' => $role,
            'user_id' => $userId,
        ])->paginate(25)->withQueryString();

        return view('admin.system.audit', array_merge(AdminPortal::layoutData($request), [
            'logs' => $logs,
            'modules' => AuditActionType::cases(),
            'roles' => UserRole::cases(),
            'actors' => User::query()
                ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin, UserRole::Faculty])
                ->orderBy('name')
                ->get(['id', 'name', 'account_id', 'role']),
            'filters' => [
                'search' => $search ?? '',
                'from' => $from ?? '',
                'to' => $to ?? '',
                'module' => $module ?? '',
                'role' => $role ?? '',
                'user_id' => $userId ? (string) $userId : '',
            ],
        ]));
    }
}
