<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use Illuminate\Validation\Rule;

class StoreStaffUserRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'string', 'max:50', 'unique:users,account_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'staff_role_id' => [
                'nullable',
                'integer',
                Rule::exists('staff_roles', 'id')->where(fn ($query) => $query->where('slug', '!=', 'chief_super_admin')),
            ],
            'send_enrollment_email' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.unique' => 'This account ID is already in use.',
            'email.unique' => 'This email is already in use.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_id' => trim((string) $this->input('account_id')),
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'send_enrollment_email' => $this->boolean('send_enrollment_email'),
        ]);
    }
}
