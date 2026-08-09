<?php

namespace App\Enums;

enum AnnouncementRelatedModule: string
{
    case None = 'none';
    case Election = 'election';
    case TalentCompetition = 'talent_competition';
    case SchoolEvent = 'school_event';
    case Fundraising = 'fundraising';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Election => 'Election',
            self::TalentCompetition => 'Talent Competition',
            self::SchoolEvent => 'School Event',
            self::Fundraising => 'Fundraising',
        };
    }

    public function viewLabel(): string
    {
        return match ($this) {
            self::None => '',
            self::Election => 'View Election',
            self::TalentCompetition => 'View Competition',
            self::SchoolEvent => 'View Event',
            self::Fundraising => 'View Campaign',
        };
    }
}
