<?php

namespace App\Enums;

enum TalentJudgeScoreStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
        };
    }

    public function isLocked(): bool
    {
        return $this === self::Submitted;
    }
}
