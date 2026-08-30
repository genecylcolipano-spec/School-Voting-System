<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use App\Models\Passkey;

class PasskeyActionRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:revoke,lost'],
        ];
    }

    public function passkey(): Passkey
    {
        return $this->route('passkey');
    }
}
