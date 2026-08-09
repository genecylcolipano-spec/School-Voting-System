<?php

namespace App\Http\Controllers\Admin\System;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = $request->string('search')->toString() ?: null;
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;
        $module = $request->string('module')->toString() ?: null;
        $role = $request->string('role')->toString() ?: null;
        $userId = $request->integer('user_id') ?: null;

        $logs = AuditLog::query()
            ->with('user:id,name,account_id,role')
            ->when($search, function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('action', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('account_id', 'like', $term));
                });
            })
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($module, fn ($q) => $q->where('action_type', $module))
            ->when($role, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('role', $role)))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->paginate(25)
            ->withQueryString();

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
