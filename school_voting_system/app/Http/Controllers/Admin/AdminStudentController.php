<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\StudentStatus;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentRecordRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use App\Services\Admin\UserAccountLifecycleService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected AdminScopeService $scope,
        protected UserAccountLifecycleService $lifecycle,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAnyStudents');

        $status = $request->string('status')->toString();

        $students = $this->scope->manageableStudentsQuery($request->user())
            ->withCount(['passkeys', 'votes', 'donations'])
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
                    $query->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('account_id', 'like', $term);
                });
            })
            ->when($request->filled('grade_level'), fn ($query) => $query->where('grade_level', $request->string('grade_level')))
            ->when($request->filled('section'), fn ($query) => $query->where('section', $request->string('section')))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true)->whereNull('archived_at'))
            ->when(in_array($status, ['suspended', 'inactive'], true), fn ($q) => $q->where('is_active', false)->whereNull('archived_at'))
            ->when(in_array($status, ['deactivated', 'archived'], true), fn ($q) => $q->whereNotNull('archived_at'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'students' => $students,
            'isScoped' => ! $request->user()->isSuperAdmin(),
            'gradeLevels' => $this->scope->assignableGradeLevels($request->user()),
            'sections' => $this->scope->assignableSections($request->user()),
            'statusFilter' => $status,
        ]);
    }

    public function show(Request $request, User $student): View
    {
        $this->authorize('updateStudentRecord', $student);
        abort_unless($student->isStudent(), 404);

        $student->loadCount('passkeys');

        return view('admin.students.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'account' => $student,
            'devices' => $this->lifecycle->devicesFor($student),
            'loginHistory' => $this->lifecycle->loginHistoryFor($student),
        ]);
    }

    public function edit(Request $request, User $student): View
    {
        $this->authorize('updateStudentRecord', $student);

        abort_unless($student->isStudent(), 404);

        return view('admin.students.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'student' => $student,
            'gradeLevels' => $this->scope->assignableGradeLevels($request->user()),
            'sections' => $this->scope->assignableSections($request->user()),
            'statuses' => StudentStatus::cases(),
        ]);
    }

    public function update(UpdateStudentRecordRequest $request, User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $validated = $request->validated();

        $student->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
            'student_status' => $validated['student_status'],
        ]);

        if ($student->isDirty('email')) {
            $student->email_verified_at = null;
        }

        $student->save();

        $this->logAdminAction(
            "Updated student record for {$student->name} ({$student->account_id})",
            AuditActionType::User,
            User::class,
            $student->id,
            [
                'name' => $student->name,
                'email' => $student->email,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'student_status' => $student->student_status?->value,
            ],
        );

        return redirect()
            ->route('admin.students.index', ['q' => $student->account_id])
            ->with('success', 'Student record updated successfully.');
    }

    public function toggleActive(Request $request, User $student): RedirectResponse
    {
        $this->authorize('updateStudentRecord', $student);
        abort_unless($student->isStudent(), 404);

        if ($student->isArchived()) {
            return back()->with('error', 'Restore this deactivated account before changing status.');
        }

        $student->forceFill(['is_active' => ! $student->is_active])->save();

        $this->logAdminAction(
            ($student->is_active ? 'Activated' : 'Suspended').' student '.$student->account_id,
            AuditActionType::User,
            User::class,
            $student->id,
        );

        return back()->with(
            'success',
            $student->is_active
                ? 'Student account activated.'
                : 'Student account suspended. They cannot sign in until reactivated.'
        );
    }

    public function archive(Request $request, User $student): RedirectResponse
    {
        $this->authorize('updateStudentRecord', $student);
        abort_unless($student->isStudent(), 404);

        $this->lifecycle->archive($student);

        $this->logAdminAction(
            'Deactivated student '.$student->account_id,
            AuditActionType::User,
            User::class,
            $student->id,
        );

        return back()->with('success', 'Student account deactivated. Voting history is preserved.');
    }

    public function restore(Request $request, User $student): RedirectResponse
    {
        $this->authorize('updateStudentRecord', $student);
        abort_unless($student->isStudent(), 404);

        $this->lifecycle->restore($student);

        $this->logAdminAction(
            'Restored student '.$student->account_id,
            AuditActionType::User,
            User::class,
            $student->id,
        );

        return back()->with('success', 'Student account restored and activated.');
    }
}
