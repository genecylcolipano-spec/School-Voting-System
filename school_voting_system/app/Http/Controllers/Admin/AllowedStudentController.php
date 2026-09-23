<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Admin\Concerns\ManagesInstitutionalRoster;
use App\Http\Controllers\Controller;
use App\Models\AllowedStudent;
use App\Services\Admin\InstitutionalRosterImportService;
use App\Services\Admin\StudentRosterYearlySyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllowedStudentController extends Controller
{
    use LogsAdminActions;
    use ManagesInstitutionalRoster;

    protected function rosterModelClass(): string
    {
        return AllowedStudent::class;
    }

    protected function rosterRoutePrefix(): string
    {
        return 'super-admin.roster.students';
    }

    protected function rosterLabel(): string
    {
        return 'Student';
    }

    protected function rosterIdLabel(): string
    {
        return 'Student ID';
    }

    protected function rosterColumns(): array
    {
        return ['account_id', 'first_name', 'last_name', 'grade_level', 'section'];
    }

    protected function templateSampleRow(): array
    {
        return ['2026-00002', 'Maria', 'Santos', '10', 'A'];
    }

    protected function extraFieldDefinitions(): array
    {
        return [
            ['name' => 'grade_level', 'label' => 'Grade', 'required' => false],
            ['name' => 'section', 'label' => 'Section', 'required' => false],
            ['name' => 'school_year', 'label' => 'School year', 'required' => false],
        ];
    }

    protected function rosterValidationRules(?Model $record = null): array
    {
        return [
            'account_id' => ['required', 'string', 'max:50', $this->uniqueRosterAccountIdRule($record)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'grade_level' => ['nullable', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
            'school_year' => ['nullable', 'string', 'max:20', 'regex:/^(\d{4}-\d{4})?$/'],
        ];
    }

    protected function mapImportAttributes(array $row): array
    {
        $service = app(InstitutionalRosterImportService::class);

        return [
            'first_name' => trim((string) ($row['first_name'] ?? '')),
            'last_name' => trim((string) ($row['last_name'] ?? '')),
            'grade_level' => $service->nullableString($row['grade_level'] ?? null),
            'section' => $service->nullableString($row['section'] ?? null),
        ];
    }

    public function index(Request $request): View
    {
        return $this->rosterIndex($request);
    }

    public function create(Request $request): View
    {
        return $this->rosterCreate($request);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->rosterStore($request);
    }

    public function show(Request $request, AllowedStudent $allowedStudent): View
    {
        return $this->rosterShow($request, $allowedStudent);
    }

    public function edit(Request $request, AllowedStudent $allowedStudent): View
    {
        return $this->rosterEdit($request, $allowedStudent);
    }

    public function update(Request $request, AllowedStudent $allowedStudent): RedirectResponse
    {
        return $this->rosterUpdate($request, $allowedStudent);
    }

    public function archive(Request $request, AllowedStudent $allowedStudent): RedirectResponse
    {
        return $this->rosterArchive($request, $allowedStudent);
    }

    public function restore(Request $request, AllowedStudent $allowedStudent): RedirectResponse
    {
        return $this->rosterRestore($request, $allowedStudent);
    }

    public function destroy(Request $request, AllowedStudent $allowedStudent): RedirectResponse
    {
        return $this->rosterDestroy($request, $allowedStudent);
    }

    public function export(): StreamedResponse
    {
        return $this->rosterExport();
    }

    public function importForm(Request $request): View
    {
        return $this->rosterImportForm($request);
    }

    public function importStore(Request $request): RedirectResponse
    {
        return $this->rosterImportStore($request);
    }

    public function importTemplate(): StreamedResponse
    {
        return $this->rosterImportTemplate();
    }

    public function yearSyncForm(Request $request, StudentRosterYearlySyncService $sync): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.rosters.year-sync', array_merge($this->sharedRosterViewData($request), [
            'suggestedSchoolYear' => StudentRosterYearlySyncService::suggestedSchoolYear(),
            'preview' => $sync->cachedPreview($request->user()->id),
        ]));
    }

    public function yearSyncPreview(Request $request, StudentRosterYearlySyncService $sync): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'school_year' => ['required', 'string', 'max:20'],
            'archive_missing' => ['sometimes', 'boolean'],
        ]);

        try {
            $schoolYear = StudentRosterYearlySyncService::normalizeSchoolYear($validated['school_year']);
            $preview = $sync->stashPreview(
                $request->user()->id,
                $request->file('csv_file'),
                $schoolYear,
                $request->boolean('archive_missing'),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['csv_file' => $exception->getMessage()]);
        }

        return redirect()
            ->route('super-admin.roster.students.year-sync')
            ->with('success', sprintf(
                'Preview ready for SY %s: %d add, %d update, %d restore, %d archive.',
                $preview['school_year'],
                $preview['counts']['created'],
                $preview['counts']['updated'],
                $preview['counts']['restored'],
                $preview['counts']['archived'],
            ));
    }

    public function yearSyncApply(Request $request, StudentRosterYearlySyncService $sync): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        try {
            $preview = $sync->applyCachedPlan($request->user()->id);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('super-admin.roster.students.year-sync')
                ->withErrors(['csv_file' => $exception->getMessage()]);
        }

        $this->logAdminAction(
            'Applied student roster yearly sync for SY '.$preview['school_year'],
            AuditActionType::User,
            metadata: $preview['counts'],
        );

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', sprintf(
                'School year %s synced: %d added, %d updated, %d restored, %d unchanged, %d archived. Existing logins and passkeys were kept.',
                $preview['school_year'],
                $preview['counts']['created'],
                $preview['counts']['updated'],
                $preview['counts']['restored'],
                $preview['counts']['unchanged'],
                $preview['counts']['archived'],
            ));
    }

    public function yearSyncCancel(Request $request, StudentRosterYearlySyncService $sync): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        $sync->forget($request->user()->id);

        return redirect()
            ->route('super-admin.roster.students.year-sync')
            ->with('success', 'Yearly sync preview cleared.');
    }

    protected function rosterSupportsYearlySync(): bool
    {
        return true;
    }

    protected function afterRosterUpdated(Model $record): void
    {
        if ($record instanceof AllowedStudent) {
            app(StudentRosterYearlySyncService::class)->syncRegisteredUser($record);
        }
    }
}
