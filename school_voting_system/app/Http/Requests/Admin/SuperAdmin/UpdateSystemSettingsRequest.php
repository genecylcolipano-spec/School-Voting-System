<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingsRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'system_name' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'school_logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'enable_student_registration' => ['nullable', 'boolean'],
            'enable_faculty_registration' => ['nullable', 'boolean'],
            'enable_elections' => ['nullable', 'boolean'],
            'enable_talent_voting' => ['nullable', 'boolean'],
            'enable_fundraising' => ['nullable', 'boolean'],
            'announcement_default_visibility' => ['nullable', Rule::in(['all', 'students', 'faculty', 'admins'])],
            'announcement_default_expiration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'session_timeout_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'ip_whitelist_enabled' => ['nullable', 'boolean'],
            'ip_whitelist' => ['nullable', 'string'],
            'two_factor_recovery_enabled' => ['nullable', 'boolean'],
            'public_results_published' => ['nullable', 'boolean'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_team_label' => ['nullable', 'string', 'max:120'],
        ];
    }
}
