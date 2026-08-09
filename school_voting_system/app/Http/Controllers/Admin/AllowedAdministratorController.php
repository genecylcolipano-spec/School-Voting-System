<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Admin\Concerns\ManagesInstitutionalRoster;
use App\Http\Controllers\Controller;
use App\Models\AllowedAdministrator;
use App\Services\Admin\InstitutionalRosterImportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllowedAdministratorController extends Controller
{
    use LogsAdminActions;
    use ManagesInstitutionalRoster;

    protected function rosterModelClass(): string
    {
        return AllowedAdministrator::class;
    }

    protected function rosterRoutePrefix(): string
    {
        return 'super-admin.roster.administrators';
    }

    protected function rosterLabel(): string
    {
        return 'Administrator';
    }

    protected function rosterIdLabel(): string
    {
        return 'Employee ID';
    }

    protected function rosterColumns(): array
    {
        return ['account_id', 'first_name', 'last_name', 'department', 'position'];
    }

    protected function templateSampleRow(): array
    {
        return ['EMP-001', 'Jose', 'Cruz', 'Registrar', 'Officer'];
    }

    protected function extraFieldDefinitions(): array
    {
        return [
            ['name' => 'department', 'label' => 'Office / Department', 'required' => false],
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

    public function show(Request $request, AllowedAdministrator $allowedAdministrator): View
    {
        return $this->rosterShow($request, $allowedAdministrator);
    }

    public function edit(Request $request, AllowedAdministrator $allowedAdministrator): View
    {
        return $this->rosterEdit($request, $allowedAdministrator);
    }

    public function update(Request $request, AllowedAdministrator $allowedAdministrator): RedirectResponse
    {
        return $this->rosterUpdate($request, $allowedAdministrator);
    }

    public function archive(Request $request, AllowedAdministrator $allowedAdministrator): RedirectResponse
    {
        return $this->rosterArchive($request, $allowedAdministrator);
    }

    public function restore(Request $request, AllowedAdministrator $allowedAdministrator): RedirectResponse
    {
        return $this->rosterRestore($request, $allowedAdministrator);
    }

    public function destroy(Request $request, AllowedAdministrator $allowedAdministrator): RedirectResponse
    {
        return $this->rosterDestroy($request, $allowedAdministrator);
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
