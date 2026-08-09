<?php

namespace App\Jobs;

use App\Enums\AuditActionType;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendVotingRemindersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $adminId) {}

    public function handle(
        AdminScopeService $scope,
        PortalNotificationService $notifications,
        AuditLogService $audit,
    ): void {
        $admin = User::query()->find($this->adminId);

        if (! $admin || ! $scope->canSendReminders($admin)) {
            return;
        }

        $election = $scope->assignedElection($admin);
        $students = $scope->notVotedStudents($admin);

        foreach ($students as $student) {
            $notifications->sendVotingReminder($student, $admin, $election);
        }

        $audit->record(
            $admin,
            "Sent voting reminders to {$students->count()} students"
                .($election ? " for {$election->title}" : ''),
            AuditActionType::User,
        );
    }
}
