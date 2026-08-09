<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentJudgeRole;
use App\Enums\UserRole;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdmin\StoreStaffUserRequest;
use App\Http\Requests\Admin\SuperAdmin\UpdateStaffUserRequest;
use App\Models\AuditLog;
use App\Models\StaffRole;
use App\Models\TalentEvent;
use App\Models\TalentEventJudge;
use App\Models\User;
use App\Services\Admin\StaffAccountService;
use App\Services\Admin\UserAccountLifecycleService;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use App\Services\Talent\TalentJudgingService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SuperAdminStaffUserController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected StaffAccountService $staffAccounts,
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
        protected UserAccountLifecycleService $lifecycle,
        protected TalentJudgingService $judging,
    ) {}

    public function administratorsIndex(Request $request): View
    {
        return $this->index($request, UserRole::Admin);
    }

    public function facultyIndex(Request $request): View
    {
        return $this->index($request, UserRole::Faculty);
    }

    public function administratorsCreate(Request $request): View
    {
        return $this->create($request, UserRole::Admin);
    }

    public function facultyCreate(Request $request): View
    {
        return $this->create($request, UserRole::Faculty);
    }

    public function administratorsStore(StoreStaffUserRequest $request): RedirectResponse
    {
        return $this->store($request, UserRole::Admin);
    }

    public function facultyStore(StoreStaffUserRequest $request): RedirectResponse
    {
        return $this->store($request, UserRole::Faculty);
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);

        $user->load(['staffRole'])->loadCount('passkeys');

        $assignedCompetitions = collect();
        $assignableCompetitions = collect();

        if ($user->isFaculty()) {
            $assignedCompetitions = TalentEventJudge::query()
                ->active()
                ->where('user_id', $user->id)
                ->with(['talentEvent' => fn ($q) => $q->withTrashed()])
                ->latest('assigned_at')
                ->get();

            $assignedIds = $assignedCompetitions->pluck('talent_event_id')->all();
            $assignableCompetitions = $this->judging
                ->eligibleCompetitionsForAssignmentQuery($assignedIds)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'voting_method',
                    'status',
                    'talent_category',
                    'type',
                    'event_date',
                    'voting_starts_at',
                    'voting_ends_at',
                    'registration_starts_at',
                    'registration_ends_at',
                    'published_to_students',
                    'is_paused',
                ]);
        }

        return view('admin.staff-users.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'account' => $user,
            'devices' => $this->lifecycle->devicesFor($user),
            'loginHistory' => $this->lifecycle->loginHistoryFor($user),
            'removalBlockers' => $this->lifecycle->removalBlockers($user),
            'assignedCompetitions' => $assignedCompetitions,
            'assignableCompetitions' => $assignableCompetitions,
            'judgeRoles' => TalentJudgeRole::cases(),
            'indexRoute' => $user->isFaculty()
                ? route('super-admin.faculty.index')
                : route('super-admin.administrators.index'),
            'editRoute' => $user->isFaculty()
                ? route('super-admin.faculty.edit', $user)
                : route('super-admin.administrators.edit', $user),
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);

        return view('admin.staff-users.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'account' => $user,
            'role' => $user->role,
            'pageTitle' => $user->isFaculty() ? 'Edit Faculty' : 'Edit Administrator',
            'updateRoute' => $user->isFaculty()
                ? route('super-admin.faculty.update', $user)
                : route('super-admin.administrators.update', $user),
            'indexRoute' => $user->isFaculty()
                ? route('super-admin.faculty.index')
                : route('super-admin.administrators.index'),
            'staffRoles' => $user->isAdmin()
                ? StaffRole::query()->where('slug', '!=', 'chief_super_admin')->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(UpdateStaffUserRequest $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);

        $updated = $this->staffAccounts->update($user, $request->validated(), $request->user());

        $this->logAdminAction(
            'Updated '.$updated->role->value.' account '.$updated->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $updated->id,
        );

        $showRoute = $updated->isFaculty()
            ? 'super-admin.faculty.show'
            : 'super-admin.administrators.show';

        return redirect()
            ->route($showRoute, $updated)
            ->with('success', 'Account updated successfully.');
    }

    public function sendEnrollment(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);

        $delivery = $this->enrollmentLinks->sendToUser($user);

        $this->logAdminAction(
            'Issued passkey enrollment/reset link for '.$user->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $user->id,
            metadata: [
                'email_sent' => $delivery['email_sent'],
                'role' => $user->role?->value,
            ],
        );

        $message = $delivery['email_sent']
            ? 'Passkey reset / enrollment link emailed to '.$delivery['recipient'].'.'
            : ($delivery['email_error'] ?: 'Passkey reset / enrollment link generated. Share it manually.');

        return back()
            ->with('success', $message)
            ->with('enrollment_url', $delivery['url']);
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);
        abort_if($user->id === $request->user()->id, 403, 'You cannot deactivate your own account.');

        if ($user->isArchived()) {
            return back()->with('error', 'Restore this archived account before changing active status.');
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $this->logAdminAction(
            ($user->is_active ? 'Activated' : 'Deactivated').' '.$user->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $user->id,
        );

        return back()->with(
            'success',
            $user->is_active
                ? 'Account activated.'
                : 'Account deactivated. They cannot sign in until reactivated.'
        );
    }

    public function archive(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);
        abort_if($user->id === $request->user()->id, 403, 'You cannot archive your own account.');

        $this->lifecycle->archive($user);

        $this->logAdminAction(
            'Archived '.$user->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $user->id,
        );

        return back()->with('success', 'Account archived and deactivated.');
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);

        $this->lifecycle->restore($user);

        $this->logAdminAction(
            'Restored '.$user->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $user->id,
        );

        return back()->with('success', 'Account restored and activated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Faculty], true), 404);
        abort_if($user->id === $request->user()->id, 403, 'You cannot remove your own account.');

        try {
            $this->lifecycle->assertCanRemove($user);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        $role = $user->role;
        $accountId = $user->account_id;
        $name = $user->name;

        $user->delete();

        $this->logAdminAction(
            'Removed '.$role->value.' account '.$accountId,
            AuditActionType::User,
            metadata: ['name' => $name, 'account_id' => $accountId],
        );

        $indexRoute = $role === UserRole::Faculty
            ? 'super-admin.faculty.index'
            : 'super-admin.administrators.index';

        return redirect()
            ->route($indexRoute)
            ->with('success', 'Account removed.');
    }

    public function assignCompetition(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless($user->isFaculty(), 404);

        $validated = $request->validate([
            'talent_event_id' => [
                'required',
                'integer',
                Rule::exists('talent_events', 'id'),
            ],
            'judge_role' => ['required', Rule::enum(TalentJudgeRole::class)],
        ]);

        $event = TalentEvent::query()->findOrFail($validated['talent_event_id']);
        $role = TalentJudgeRole::from($validated['judge_role']);

        $assignment = $this->judging->assignJudge($event, $user, $request->user(), $role);

        $this->logAdminAction(
            "Assigned {$user->account_id} as {$role->label()} for {$event->title}",
            AuditActionType::Judging,
            targetType: User::class,
            targetId: $user->id,
            metadata: [
                'action' => 'assigned_judge',
                'talent_event_id' => $event->id,
                'competition' => $event->title,
                'faculty_id' => $user->id,
                'faculty_name' => $user->name,
                'judge_role' => $role->value,
                'assignment_id' => $assignment->id,
                'super_admin_id' => $request->user()->id,
            ],
        );

        return back()->with('success', "Assigned as {$role->label()} for \"{$event->title}\".");
    }

    public function updateCompetitionRole(Request $request, User $user, int $talentEvent): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless($user->isFaculty(), 404);

        $talentEvent = TalentEvent::withTrashed()->findOrFail($talentEvent);
        abort_if($talentEvent->trashed(), 404, 'This competition is archived and cannot update judge roles.');

        $validated = $request->validate([
            'judge_role' => ['required', Rule::enum(TalentJudgeRole::class)],
        ]);

        $role = TalentJudgeRole::from($validated['judge_role']);
        $assignment = $this->judging->updateJudgeRole($talentEvent, $user, $role, $request->user());

        $this->logAdminAction(
            "Updated {$user->account_id} judge role to {$role->label()} for {$talentEvent->title}",
            AuditActionType::Judging,
            targetType: User::class,
            targetId: $user->id,
            metadata: [
                'action' => 'updated_judge_role',
                'talent_event_id' => $talentEvent->id,
                'competition' => $talentEvent->title,
                'faculty_id' => $user->id,
                'faculty_name' => $user->name,
                'judge_role' => $role->value,
                'assignment_id' => $assignment->id,
                'super_admin_id' => $request->user()->id,
            ],
        );

        return back()->with('success', "Judge role updated to {$role->label()}.");
    }

    public function removeCompetition(Request $request, User $user, int $talentEvent): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless($user->isFaculty(), 404);

        $talentEvent = TalentEvent::withTrashed()->findOrFail($talentEvent);

        $validated = $request->validate([
            'removal_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->judging->removeJudge(
            $talentEvent,
            $user,
            $request->user(),
            $validated['removal_reason'] ?? null,
        );

        $this->logAdminAction(
            "Removed {$user->account_id} from judging {$talentEvent->title}",
            AuditActionType::Judging,
            targetType: User::class,
            targetId: $user->id,
            metadata: [
                'action' => 'removed_judge',
                'talent_event_id' => $talentEvent->id,
                'competition' => $talentEvent->title,
                'faculty_id' => $user->id,
                'faculty_name' => $user->name,
                'removal_reason' => $validated['removal_reason'] ?? null,
                'super_admin_id' => $request->user()->id,
            ],
        );

        return back()->with('success', "Removed from \"{$talentEvent->title}\".");
    }

    protected function index(Request $request, UserRole $role): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $status = $request->string('status')->toString();

        $users = User::query()
            ->where('role', $role)
            ->with(['staffRole', 'facultyRoster', 'administratorRoster'])
            ->withCount('passkeys')
            ->when($role === UserRole::Faculty, fn ($q) => $q->withCount('judgingAssignments'))
            ->addSelect([
                'last_login_at' => AuditLog::query()
                    ->select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->where('action_type', AuditActionType::Auth)
                    ->where('action', 'like', '%login%')
                    ->latest('created_at')
                    ->limit(1),
            ])
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($term) {
                    $query->where('account_id', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true)->whereNull('archived_at'))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false)->whereNull('archived_at'))
            ->when($status === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->orderBy('account_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff-users.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'role' => $role,
            'accounts' => $users,
            'summary' => $this->lifecycle->roleSummary($role),
            'statusFilter' => $status,
            'pageTitle' => $role === UserRole::Faculty ? 'Faculty' : 'Administrators',
            'createRoute' => $role === UserRole::Faculty
                ? route('super-admin.faculty.create')
                : route('super-admin.administrators.create'),
            'indexRouteName' => $role === UserRole::Faculty
                ? 'super-admin.faculty.index'
                : 'super-admin.administrators.index',
            'showRouteName' => $role === UserRole::Faculty
                ? 'super-admin.faculty.show'
                : 'super-admin.administrators.show',
            'editRouteName' => $role === UserRole::Faculty
                ? 'super-admin.faculty.edit'
                : 'super-admin.administrators.edit',
        ]);
    }

    protected function create(Request $request, UserRole $role): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.staff-users.create', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'role' => $role,
            'pageTitle' => $role === UserRole::Faculty ? 'Create Faculty' : 'Create Administrator',
            'storeRoute' => $role === UserRole::Faculty
                ? route('super-admin.faculty.store')
                : route('super-admin.administrators.store'),
            'indexRoute' => $role === UserRole::Faculty
                ? route('super-admin.faculty.index')
                : route('super-admin.administrators.index'),
            'suggestedAccountId' => $this->staffAccounts->suggestedAccountId($role),
            'staffRoles' => $role === UserRole::Admin
                ? StaffRole::query()->where('slug', '!=', 'chief_super_admin')->orderBy('name')->get()
                : collect(),
        ]);
    }

    protected function store(StoreStaffUserRequest $request, UserRole $role): RedirectResponse
    {
        try {
            $result = $this->staffAccounts->create($role, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $this->logAdminAction(
            'Created '.$role->value.' account '.$result['user']->account_id,
            AuditActionType::User,
            targetType: User::class,
            targetId: $result['user']->id,
            metadata: [
                'email_sent' => $result['email_sent'],
            ],
        );

        $message = $role === UserRole::Faculty
            ? 'Faculty account created.'
            : 'Administrator account created.';

        if ($result['email_sent']) {
            $message .= ' Passkey enrollment email sent to '.$result['user']->email.'.';
            $message .= ' If it does not arrive, check Spam/Junk, or use the link below.';
        } elseif ($result['email_error']) {
            $message .= ' '.$result['email_error'];
        } else {
            $message .= ' Share the enrollment link below for first login.';
        }

        $indexRoute = $role === UserRole::Faculty
            ? 'super-admin.faculty.index'
            : 'super-admin.administrators.index';

        return redirect()
            ->route($indexRoute)
            ->with('success', $message)
            ->with('enrollment_url', $result['enrollment_url']);
    }
}
