<?php

namespace App\Enums;

enum TalentEventType: string
{
    case TalentCompetition = 'talent_competition';
    case Debate = 'debate';
    case Quiz = 'quiz';

    public function label(): string
    {
        return match ($this) {
            self::TalentCompetition => 'Talent Competition',
            self::Debate => 'Debate',
            self::Quiz => 'Quiz',
        };
    }
}
