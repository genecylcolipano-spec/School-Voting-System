<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use Illuminate\Database\Eloquent\Model;

final class RosterMatch
{
    public function __construct(
        public readonly string $rosterType,
        public readonly UserRole $role,
        public readonly Model $record,
        public readonly string $accountId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $gradeLevel = null,
        public readonly ?string $section = null,
        public readonly ?string $department = null,
        public readonly ?string $position = null,
    ) {}

    public static function fromStudent(AllowedStudent $record): self
    {
        return new self(
            rosterType: 'student',
            role: UserRole::Student,
            record: $record,
            accountId: $record->account_id,
            firstName: $record->first_name,
            lastName: $record->last_name,
            gradeLevel: $record->grade_level,
            section: $record->section,
        );
    }

    public static function fromFaculty(AllowedFaculty $record): self
    {
        return new self(
            rosterType: 'faculty',
            role: UserRole::Faculty,
            record: $record,
            accountId: $record->account_id,
            firstName: $record->first_name,
            lastName: $record->last_name,
            department: $record->department,
            position: $record->position,
        );
    }

    public static function fromAdministrator(AllowedAdministrator $record): self
    {
        return new self(
            rosterType: 'administrator',
            role: UserRole::Admin,
            record: $record,
            accountId: $record->account_id,
            firstName: $record->first_name,
            lastName: $record->last_name,
            department: $record->department,
            position: $record->position,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPendingPayload(string $email, string $firstName, string $lastName): array
    {
        return [
            'roster_type' => $this->rosterType,
            'roster_id' => $this->record->getKey(),
            'role' => $this->role->value,
            'account_id' => $this->accountId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'grade_level' => $this->gradeLevel,
            'section' => $this->section,
            'department' => $this->department,
            'position' => $this->position,
            // Backward-compatible key used by older pending sessions.
            'allowed_student_id' => $this->rosterType === 'student' ? $this->record->getKey() : null,
        ];
    }
}
