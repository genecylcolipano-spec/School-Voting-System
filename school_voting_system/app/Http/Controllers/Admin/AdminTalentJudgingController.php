<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentJudgeRole;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use App\Services\Talent\TalentJudgingService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTalentJudgingController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected AdminScopeService $scope,
        protected TalentJudgingService $judging,
    ) {}

    public function edit(Request $request, TalentEvent $talentEvent): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $this->judging->ensureDefaultCriteria($talentEvent);

        $user = $request->user()->load(['staffRole', 'passkeys']);
        $talentEvent->load(['judges.user', 'judgingCriteria']);

        return view('admin.talent-competition.judges', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'talentEvent' => $talentEvent,
            'availableFaculty' => $this->judging->availableFaculty($talentEvent),
            'canManageCriteria' => $this->scope->canCreateTalentEvents($user),
            // Judge assignment is Super Admin only (role separation).
            'canAssignJudges' => $user->isSuperAdmin(),
            'judgeRoles' => TalentJudgeRole::cases(),
        ]);
    }

    public function assign(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'faculty')),
            ],
            'judge_role' => ['required', Rule::enum(TalentJudgeRole::class)],
        ]);

        $faculty = User::query()->findOrFail($validated['user_id']);
        $role = TalentJudgeRole::from($validated['judge_role']);

        $assignment = $this->judging->assignJudge($talentEvent, $faculty, $request->user(), $role);

        $this->logAdminAction(
            "Assigned {$faculty->account_id} as {$role->label()} for {$talentEvent->title}",
            AuditActionType::Judging,
            targetType: User::class,
            targetId: $faculty->id,
            metadata: [
                'action' => 'assigned_judge',
                'talent_event_id' => $talentEvent->id,
                'competition' => $talentEvent->title,
                'faculty_id' => $faculty->id,
                'judge_role' => $role->value,
                'assignment_id' => $assignment->id,
                'super_admin_id' => $request->user()->id,
            ],
        );

        return back()->with('success', "{$faculty->name} has been assigned as {$role->label()}.");
    }

    public function remove(Request $request, TalentEvent $talentEvent, User $faculty): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);
        abort_unless($faculty->isFaculty(), 404);

        $validated = $request->validate([
            'removal_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->judging->removeJudge(
            $talentEvent,
            $faculty,
            $request->user(),
            $validated['removal_reason'] ?? null,
        );

        $this->logAdminAction(
            "Removed {$faculty->account_id} from judging {$talentEvent->title}",
            AuditActionType::Judging,
            targetType: User::class,
            targetId: $faculty->id,
            metadata: [
                'action' => 'removed_judge',
                'talent_event_id' => $talentEvent->id,
                'competition' => $talentEvent->title,
                'faculty_id' => $faculty->id,
                'removal_reason' => $validated['removal_reason'] ?? null,
                'super_admin_id' => $request->user()->id,
            ],
        );

        return back()->with('success', "{$faculty->name} has been removed from this competition.");
    }

    public function updateCriteria(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $validated = $request->validate([
            'criteria' => ['required', 'array', 'min:1', 'max:12'],
            'criteria.*.id' => ['nullable', 'integer'],
            'criteria.*.name' => ['required', 'string', 'max:120'],
            'criteria.*.max_points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if ($talentEvent->judgeScoreSheets()->exists()) {
            return back()->with('error', 'Criteria cannot be changed after judges have started scoring.');
        }

        $keepIds = [];
        foreach (array_values($validated['criteria']) as $index => $row) {
            $payload = [
                'name' => $row['name'],
                'max_points' => (int) $row['max_points'],
                'sort_order' => $index + 1,
            ];

            if (! empty($row['id'])) {
                $criterion = $talentEvent->judgingCriteria()->whereKey($row['id'])->first();
                if ($criterion) {
                    $criterion->update($payload);
                    $keepIds[] = $criterion->id;
                    continue;
                }
            }

            $criterion = $talentEvent->judgingCriteria()->create($payload);
            $keepIds[] = $criterion->id;
        }

        $talentEvent->judgingCriteria()->whereNotIn('id', $keepIds)->delete();

        return back()->with('success', 'Judging criteria updated.');
    }
}
