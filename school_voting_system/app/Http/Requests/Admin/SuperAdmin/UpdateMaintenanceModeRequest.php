<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;

class UpdateMaintenanceModeRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
            'return_at' => ['nullable', 'date'],
            'allow_super_admin' => ['nullable', 'boolean'],
        ];
    }
}
