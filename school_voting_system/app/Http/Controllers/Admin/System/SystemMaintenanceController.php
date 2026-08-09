<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\UpdateMaintenanceModeRequest;
use App\Services\SuperAdmin\MaintenanceModeService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemMaintenanceController extends Controller
{
    public function __construct(protected MaintenanceModeService $maintenance) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.system.maintenance', array_merge(AdminPortal::layoutData($request), [
            'status' => $this->maintenance->status(),
        ]));
    }

    public function enable(UpdateMaintenanceModeRequest $request): RedirectResponse
    {
        $this->maintenance->enable($request->user(), [
            'message' => $request->validated('message'),
            'return_at' => $request->validated('return_at'),
            'allow_super_admin' => $request->boolean('allow_super_admin', true),
        ]);

        return back()->with('success', 'Maintenance mode enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $this->maintenance->disable($request->user());

        return back()->with('success', 'Maintenance mode disabled. The system is online.');
    }

    public function update(UpdateMaintenanceModeRequest $request): RedirectResponse
    {
        $this->maintenance->updateMessage($request->user(), [
            'message' => $request->validated('message'),
            'return_at' => $request->validated('return_at'),
            'allow_super_admin' => $request->boolean('allow_super_admin', true),
        ]);

        return back()->with('success', 'Maintenance settings updated.');
    }
}
