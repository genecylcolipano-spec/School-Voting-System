<?php

namespace App\Http\Requests\Admin;

use App\Models\User;

class IssuePasskeyResetRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        return $targetUser instanceof User
            && ($this->user()?->can('issuePasskeyReset', $targetUser) ?? false);
    }

    public function rules(): array
    {
        return [
            'recovery_request_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
