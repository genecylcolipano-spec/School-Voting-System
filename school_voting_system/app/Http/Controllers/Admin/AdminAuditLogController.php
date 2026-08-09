<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminScopeService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function __construct(protected AdminScopeService $scope) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $search = $request->string('search')->toString() ?: null;
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;
        $module = $request->string('module')->toString() ?: null;
        $role = $request->string('role')->toString() ?: null;

        return view('admin.audit.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'logs' => $this->scope->auditLogsQuery($user, $search, $from, $to, $module, $role)
                ->paginate(25)
                ->withQueryString(),
            'modules' => AuditActionType::cases(),
            'filters' => [
                'search' => $search ?? '',
                'from' => $from ?? '',
                'to' => $to ?? '',
                'module' => $module ?? '',
                'role' => $role ?? '',
            ],
        ]);
    }
}
