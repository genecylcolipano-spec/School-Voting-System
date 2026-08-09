<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudentStatus;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
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
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'student_status' => ['required', Rule::enum(StudentStatus::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $actor = $this->user();
            if (! $actor || $actor->isSuperAdmin()) {
                return;
            }

            $scope = app(AdminScopeService::class);
            $grade = (string) $this->input('grade_level');
            $section = (string) $this->input('section');

            $allowedGrades = $scope->assignableGradeLevels($actor);
            if ($allowedGrades !== [] && ! in_array($grade, $allowedGrades, true)) {
                $validator->errors()->add('grade_level', 'Grade level is outside your assigned scope.');
            }

            $allowedSections = $scope->assignableSections($actor);
            if ($allowedSections !== [] && ! in_array($section, $allowedSections, true)) {
                $validator->errors()->add('section', 'Section is outside your assigned scope.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grade_level' => trim((string) $this->input('grade_level')),
            'section' => trim((string) $this->input('section')),
        ]);
    }
}
