<?php

namespace App\Enums;

enum AnnouncementCategory: string
{
    case General = 'general';
    case Election = 'election';
    case TalentCompetition = 'talent_competition';
    case SchoolEvent = 'school_event';
    case Fundraising = 'fundraising';
    case Academic = 'academic';
    case Emergency = 'emergency';
    case Maintenance = 'maintenance';
    case Holiday = 'holiday';
    case Others = 'others';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Election => 'Election',
            self::TalentCompetition => 'Talent Competition',
            self::SchoolEvent => 'School Event',
            self::Fundraising => 'Fundraising',
            self::Academic => 'Academic',
            self::Emergency => 'Emergency',
            self::Maintenance => 'Maintenance',
            self::Holiday => 'Holiday',
            self::Others => 'Others',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::General => 'border-slate-500/30 bg-slate-500/10 text-slate-200',
            self::Election => 'border-violet-500/30 bg-violet-500/10 text-violet-200',
            self::TalentCompetition => 'border-fuchsia-500/30 bg-fuchsia-500/10 text-fuchsia-200',
            self::SchoolEvent => 'border-cyan-500/30 bg-cyan-500/10 text-cyan-200',
            self::Fundraising => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200',
            self::Academic => 'border-blue-500/30 bg-blue-500/10 text-blue-200',
            self::Emergency => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
            self::Maintenance => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
            self::Holiday => 'border-pink-500/30 bg-pink-500/10 text-pink-200',
            self::Others => 'border-slate-500/30 bg-slate-500/10 text-slate-300',
        };
    }
}
