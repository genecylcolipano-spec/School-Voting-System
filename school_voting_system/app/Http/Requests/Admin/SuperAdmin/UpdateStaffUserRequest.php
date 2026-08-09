<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateStaffUserRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        /** @var User $account */
        $account = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($account?->id),
            ],
            'staff_role_id' => [
                'nullable',
                'integer',
                Rule::exists('staff_roles', 'id')->where(fn ($query) => $query->where('slug', '!=', 'chief_super_admin')),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
