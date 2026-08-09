<?php

namespace App\Enums;

enum TalentRankingMethod: string
{
    case Votes = 'votes';
    case Points = 'points';

    public function label(): string
    {
        return match ($this) {
            self::Votes => 'Highest Votes',
            self::Points => 'Highest Points',
        };
    }
}
