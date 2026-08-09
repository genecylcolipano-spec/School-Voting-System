<?php

namespace App\Enums;

enum TalentVotingMethod: string
{
    case StudentOnly = 'student_only';
    case JudgesOnly = 'judges_only';
    case JudgesAndStudents = 'judges_and_students';

    public function label(): string
    {
        return match ($this) {
            self::StudentOnly => 'Student Voting Only',
            self::JudgesOnly => 'Judges Only',
            self::JudgesAndStudents => 'Judges + Student Voting',
        };
    }

    public function requiresHybridPercentages(): bool
    {
        return $this === self::JudgesAndStudents;
    }
}
