<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use App\Models\Passkey;

class PasskeyActionRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:revoke,reassign,expiry,lost'],
            'reassigned_to_user_id' => ['nullable', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    public function passkey(): Passkey
    {
        return $this->route('passkey');
    }
}
