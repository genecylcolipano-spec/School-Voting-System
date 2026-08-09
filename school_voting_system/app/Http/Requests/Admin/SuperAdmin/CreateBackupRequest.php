<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use App\Services\SuperAdmin\BackupService;
use Illuminate\Validation\Rule;

class CreateBackupRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'type' => [
                'nullable',
                Rule::in([
                    BackupService::TYPE_FULL,
                    BackupService::TYPE_ELECTION_RESULTS,
                    BackupService::TYPE_STUDENT_DATA,
                    BackupService::TYPE_ADMIN_ACCOUNTS,
                ]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('type')) {
            $this->merge(['type' => BackupService::TYPE_FULL]);
        }
    }
}
