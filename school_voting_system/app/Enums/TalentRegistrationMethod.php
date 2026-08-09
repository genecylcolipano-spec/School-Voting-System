<?php

namespace App\Enums;

enum TalentRegistrationMethod: string
{
    case AdminManaged = 'admin';
    case StudentRegistration = 'student';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::AdminManaged => 'Admin Managed',
            self::StudentRegistration => 'Student Registration',
            self::Both => 'Both',
        };
    }

    public function allowsStudentRegistration(): bool
    {
        return $this === self::StudentRegistration || $this === self::Both;
    }

    public function allowsAdminManaged(): bool
    {
        return $this === self::AdminManaged || $this === self::Both;
    }
}
