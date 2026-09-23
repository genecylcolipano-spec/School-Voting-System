<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudentStatus;
use App\Models\User;
use App\Support\ContactPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');

        return $student instanceof User
            && $this->user()?->can('updateStudentRecord', $student);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $student */
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($student->id),
            ],
            'phone' => ContactPhone::rules(),
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'school_year' => ['nullable', 'string', 'max:20', 'regex:/^(\d{4}-\d{4})?$/'],
            'student_status' => ['required', Rule::enum(StudentStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ContactPhone::normalize($this->input('phone')),
            'grade_level' => trim((string) $this->input('grade_level')),
            'section' => trim((string) $this->input('section')),
            'school_year' => trim((string) $this->input('school_year')) ?: null,
        ]);
    }
}
