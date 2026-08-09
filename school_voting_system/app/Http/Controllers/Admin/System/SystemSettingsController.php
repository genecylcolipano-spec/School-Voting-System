<?php

namespace App\Http\Controllers\Admin\System;

use App\Enums\AuditActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\UpdateSystemSettingsRequest;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\SuperAdmin\SystemSettingsService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SystemSettingsService $settings,
        protected AuditLogService $audit,
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $settings = $this->settings->all();

        return view('admin.system.settings', array_merge(AdminPortal::layoutData($request), [
            'settings' => $settings,
            'logoUrl' => $this->settings->schoolLogoUrl(),
            'ipWhitelistText' => is_array($settings['ip_whitelist'] ?? null)
                ? implode(', ', $settings['ip_whitelist'])
                : (string) ($settings['ip_whitelist'] ?? ''),
        ]));
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['enable_student_registration'] = $request->boolean('enable_student_registration');
        $validated['enable_faculty_registration'] = $request->boolean('enable_faculty_registration');
        $validated['enable_elections'] = $request->boolean('enable_elections');
        $validated['enable_talent_voting'] = $request->boolean('enable_talent_voting');
        $validated['enable_fundraising'] = $request->boolean('enable_fundraising');
        $validated['ip_whitelist_enabled'] = $request->boolean('ip_whitelist_enabled');
        $validated['two_factor_recovery_enabled'] = $request->boolean('two_factor_recovery_enabled');
        $validated['public_results_published'] = $request->boolean('public_results_published');

        $this->settings->update(
            $validated,
            $request->file('school_logo'),
            $request->boolean('remove_logo'),
        );

        $this->audit->record($request->user(), 'Updated System Settings', AuditActionType::System);

        return back()->with('success', 'System settings saved.');
    }
}
