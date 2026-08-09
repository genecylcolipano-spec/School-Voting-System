<?php

namespace App\Enums;

enum TalentJudgeRole: string
{
    case HeadJudge = 'head_judge';
    case Judge = 'judge';
    case ReserveJudge = 'reserve_judge';

    public function label(): string
    {
        return match ($this) {
            self::HeadJudge => 'Lead Judge',
            self::Judge => 'Judge',
            self::ReserveJudge => 'Reserve Judge',
        };
    }
}
