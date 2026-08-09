<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;

class BulkUsersRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'in:activate,deactivate,delete,export,resend_access'],
        ];
    }
}
