<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Enums\AuditActionType;
use App\Models\User;
use App\Services\Admin\InstitutionalRosterImportService;
use App\Services\Portal\PortalNotificationService;
use App\Support\AdminPortal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ManagesInstitutionalRoster
{
    abstract protected function rosterModelClass(): string;

    abstract protected function rosterRoutePrefix(): string;

    abstract protected function rosterLabel(): string;

    abstract protected function rosterIdLabel(): string;

    /** @return list<string> */
    abstract protected function rosterColumns(): array;

    /** @return list<string> */
    abstract protected function templateSampleRow(): array;

    /** @return array<string, mixed> */
    abstract protected function rosterValidationRules(?Model $record = null): array;

    /** @param  array<string, mixed>  $row @return array<string, mixed> */
    abstract protected function mapImportAttributes(array $row): array;

    /** @return list<array{name: string, label: string, required?: bool}> */
    protected function extraFieldDefinitions(): array
    {
        return [];
    }

    protected function rosterIndex(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $model = $this->rosterModelClass();
        $status = $request->string('status')->toString();

        $records = $model::query()
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($term) {
                    $query->where('account_id', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term);
                });
            })
            ->when($status === 'registered', fn ($q) => $q->where('is_registered', true)->whereNull('archived_at'))
            ->when($status === 'enrollment_pending', fn ($q) => $q->where('is_registered', false)->where('registration_status', 'enrollment_pending')->whereNull('archived_at'))
            ->when(in_array($status, ['pending', 'not_registered'], true), fn ($q) => $q->where('is_registered', false)->where(function ($inner) {
                $inner->whereNull('registration_status')
                    ->orWhere('registration_status', 'not_registered');
            })->whereNull('archived_at'))
            ->when($status === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->when($status === '', fn ($q) => $q->whereNull('archived_at'))
            ->orderBy('account_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.rosters.index', array_merge($this->sharedRosterViewData($request), [
            'records' => $records,
            'statusFilter' => $status,
            'summary' => [
                'total' => $model::query()->whereNull('archived_at')->count(),
                'registered' => $model::query()->whereNull('archived_at')->where('is_registered', true)->count(),
                'enrollment_pending' => $model::query()->whereNull('archived_at')->where('is_registered', false)->where('registration_status', 'enrollment_pending')->count(),
                'pending' => $model::query()->whereNull('archived_at')->where('is_registered', false)->where(function ($inner) {
                    $inner->whereNull('registration_status')
                        ->orWhere('registration_status', 'not_registered');
                })->count(),
                'archived' => $model::query()->whereNotNull('archived_at')->count(),
            ],
        ]));
    }

    protected function rosterShow(Request $request, Model $record): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.rosters.show', array_merge($this->sharedRosterViewData($request), [
            'record' => $record,
        ]));
    }

    protected function rosterEdit(Request $request, Model $record): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.rosters.edit', array_merge($this->sharedRosterViewData($request), [
            'record' => $record,
        ]));
    }

    protected function rosterUpdate(Request $request, Model $record): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate($this->rosterValidationRules($record));
        $record->update($this->normalizedRosterAttributes($validated));

        $this->logAdminAction(
            'Updated '.$this->rosterLabel().' roster row '.$record->account_id,
            AuditActionType::User,
            $this->rosterModelClass(),
            $record->getKey(),
        );

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', 'Roster record updated.');
    }

    protected function rosterArchive(Request $request, Model $record): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $record->forceFill(['archived_at' => now()])->save();

        $this->logAdminAction(
            'Archived '.$this->rosterLabel().' roster row '.$record->account_id,
            AuditActionType::User,
            $this->rosterModelClass(),
            $record->getKey(),
        );

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', 'Roster record archived.');
    }

    protected function rosterRestore(Request $request, Model $record): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $record->forceFill(['archived_at' => null])->save();

        $this->logAdminAction(
            'Restored '.$this->rosterLabel().' roster row '.$record->account_id,
            AuditActionType::User,
            $this->rosterModelClass(),
            $record->getKey(),
        );

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', 'Roster record restored.');
    }

    protected function rosterDestroy(Request $request, Model $record): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $linked = User::query()->where('account_id', $record->account_id)->exists();

        if (($record->is_registered || $linked) && ! $request->boolean('confirm_linked')) {
            return back()->withErrors([
                'record' => 'This roster row is linked to a registered account. Confirm removal to continue. The user account will not be deleted.',
            ]);
        }

        $accountId = $record->account_id;
        $record->delete();

        $this->logAdminAction(
            'Removed '.$this->rosterLabel().' roster row '.$accountId,
            AuditActionType::User,
            metadata: ['account_id' => $accountId],
        );

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', 'Roster record removed.');
    }

    protected function rosterExport(): StreamedResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $model = $this->rosterModelClass();
        $columns = [...$this->rosterColumns(), 'is_registered'];
        $filename = strtolower(str_replace(' ', '-', $this->rosterLabel())).'-roster-export-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($model, $columns) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);

            $model::query()->orderBy('account_id')->chunk(200, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($columns as $column) {
                        $line[] = $column === 'is_registered'
                            ? ($row->is_registered ? '1' : '0')
                            : ($row->{$column} ?? '');
                    }
                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function rosterImportForm(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.rosters.import', array_merge($this->sharedRosterViewData($request), [
            'maxRows' => InstitutionalRosterImportService::MAX_ROWS,
        ]));
    }

    protected function rosterImportStore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        try {
            $result = app(InstitutionalRosterImportService::class)->import(
                $request->file('csv_file'),
                $this->rosterModelClass(),
                $this->rosterColumns(),
                fn (array $row) => $this->mapImportAttributes($row),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['csv_file' => $exception->getMessage()]);
        }

        $this->logAdminAction(
            'Imported '.$this->rosterLabel().' roster CSV',
            AuditActionType::User,
            metadata: [
                'created' => $result['created'],
                'updated' => $result['updated'],
                'errors' => count($result['errors']),
            ],
        );

        $importedCount = (int) $result['created'] + (int) $result['updated'];
        if ($importedCount > 0) {
            app(PortalNotificationService::class)->rosterImported($importedCount, $request->user());
        }

        $message = sprintf(
            'Roster import finished: %d created, %d updated.',
            $result['created'],
            $result['updated'],
        );

        if ($result['errors'] !== []) {
            $message .= ' '.count($result['errors']).' row(s) had errors.';
        }

        return redirect()
            ->route($this->rosterRoutePrefix().'.index')
            ->with('success', $message)
            ->with('import_result', $result);
    }

    protected function rosterImportTemplate(): StreamedResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $filename = strtolower(str_replace(' ', '-', $this->rosterLabel())).'-roster-template.csv';
        $columns = $this->rosterColumns();
        $sample = $this->templateSampleRow();

        return response()->stream(function () use ($columns, $sample) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** @param  array<string, mixed>  $validated @return array<string, mixed> */
    protected function normalizedRosterAttributes(array $validated): array
    {
        $attributes = [];
        foreach ($validated as $key => $value) {
            $attributes[$key] = is_string($value) ? trim($value) : $value;
            if ($attributes[$key] === '') {
                $attributes[$key] = null;
            }
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    protected function sharedRosterViewData(Request $request): array
    {
        return [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'rosterLabel' => $this->rosterLabel(),
            'rosterIdLabel' => $this->rosterIdLabel(),
            'routePrefix' => $this->rosterRoutePrefix(),
            'columns' => $this->rosterColumns(),
            'extraFields' => $this->extraFieldDefinitions(),
        ];
    }

    protected function uniqueRosterAccountIdRule(?Model $record = null)
    {
        $table = (new ($this->rosterModelClass()))->getTable();

        return Rule::unique($table, 'account_id')->ignore($record?->getKey());
    }
}
