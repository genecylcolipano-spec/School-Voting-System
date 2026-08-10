<?php

namespace App\Enums;

enum RosterRegistrationStatus: string
{
    case NotRegistered = 'not_registered';
    case EnrollmentPending = 'enrollment_pending';
    case Registered = 'registered';

    public function label(): string
    {
        return match ($this) {
            self::NotRegistered => 'Not Registered',
            self::EnrollmentPending => 'Enrollment Pending',
            self::Registered => 'Registered',
        };
    }

    public function isFullyRegistered(): bool
    {
        return $this === self::Registered;
    }
}
