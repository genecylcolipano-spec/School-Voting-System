<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Admin\Concerns\ManagesInstitutionalRoster;
use App\Http\Controllers\Controller;
use App\Models\AllowedStudent;
use App\Services\Admin\InstitutionalRosterImportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
}
