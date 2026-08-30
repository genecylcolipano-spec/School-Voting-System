<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;

class GenerateReportRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'report' => ['required', 'in:election_summary,voter_turnout,audit_trail,passkey_inventory'],
            'election_id' => ['nullable', 'integer', 'exists:elections,id'],
            'format' => ['nullable', 'in:html,pdf'],
        ];
    }
}
