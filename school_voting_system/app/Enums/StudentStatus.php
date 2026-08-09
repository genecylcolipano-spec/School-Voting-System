<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Enrolled = 'enrolled';
    case Probation = 'probation';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Enrolled',
            self::Probation => 'Probation',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function canVote(): bool
    {
        return $this === self::Enrolled;
    }
}
