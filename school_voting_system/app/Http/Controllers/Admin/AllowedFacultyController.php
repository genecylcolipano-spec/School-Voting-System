<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Admin\Concerns\ManagesInstitutionalRoster;
use App\Http\Controllers\Controller;
use App\Models\AllowedFaculty;
use App\Services\Admin\InstitutionalRosterImportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllowedFacultyController extends Controller
{
    use LogsAdminActions;
    use ManagesInstitutionalRoster;

    protected function rosterModelClass(): string
    {
        return AllowedFaculty::class;
    }

    protected function rosterRoutePrefix(): string
    {
        return 'super-admin.roster.faculty';
    }

    protected function rosterLabel(): string
    {
        return 'Faculty';
    }

    protected function rosterIdLabel(): string
    {
        return 'Faculty ID';
    }

    protected function rosterColumns(): array
    {
        return ['account_id', 'first_name', 'last_name', 'department', 'position'];
    }

    protected function templateSampleRow(): array
    {
        return ['FAC-001', 'Ana', 'Reyes', 'Science', 'Teacher'];
    }

    protected function extraFieldDefinitions(): array
    {
        return [
            ['name' => 'department', 'label' => 'Department', 'required' => false],
            ['name' => 'position', 'label' => 'Position', 'required' => false],
        ];
    }

    protected function rosterValidationRules(?Model $record = null): array
    {
        return [
            'account_id' => ['required', 'string', 'max:50', $this->uniqueRosterAccountIdRule($record)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
        ];
    }

    protected function mapImportAttributes(array $row): array
    {
        $service = app(InstitutionalRosterImportService::class);

        return [
            'first_name' => trim((string) ($row['first_name'] ?? '')),
            'last_name' => trim((string) ($row['last_name'] ?? '')),
            'department' => $service->nullableString($row['department'] ?? null),
            'position' => $service->nullableString($row['position'] ?? null),
        ];
    }

    public function index(Request $request): View
    {
        return $this->rosterIndex($request);
    }

    public function show(Request $request, AllowedFaculty $allowedFaculty): View
    {
        return $this->rosterShow($request, $allowedFaculty);
    }

    public function edit(Request $request, AllowedFaculty $allowedFaculty): View
    {
        return $this->rosterEdit($request, $allowedFaculty);
    }

    public function update(Request $request, AllowedFaculty $allowedFaculty): RedirectResponse
    {
        return $this->rosterUpdate($request, $allowedFaculty);
    }

    public function archive(Request $request, AllowedFaculty $allowedFaculty): RedirectResponse
    {
        return $this->rosterArchive($request, $allowedFaculty);
    }

    public function restore(Request $request, AllowedFaculty $allowedFaculty): RedirectResponse
    {
        return $this->rosterRestore($request, $allowedFaculty);
    }

    public function destroy(Request $request, AllowedFaculty $allowedFaculty): RedirectResponse
    {
        return $this->rosterDestroy($request, $allowedFaculty);
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
