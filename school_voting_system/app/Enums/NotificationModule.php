<?php

namespace App\Enums;

enum NotificationModule: string
{
    case Election = 'election';
    case Competition = 'competition';
    case Fundraising = 'fundraising';
    case Announcement = 'announcement';
    case Roster = 'roster';
    case Registration = 'registration';
    case Judging = 'judging';
    case Backup = 'backup';
    case Security = 'security';
    case System = 'system';
    case Authentication = 'authentication';
    case User = 'user';
    case Event = 'event';

    public function label(): string
    {
        return match ($this) {
            self::Election => 'Election',
            self::Competition => 'Competition',
            self::Fundraising => 'Fundraising',
            self::Announcement => 'Announcement',
            self::Roster => 'Roster',
            self::Registration => 'Registration',
            self::Judging => 'Judging',
            self::Backup => 'Backup',
            self::Security => 'Security',
            self::System => 'System',
            self::Authentication => 'Authentication',
            self::User => 'User',
            self::Event => 'Event',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Election => '🗳️',
            self::Competition => '🏆',
            self::Fundraising => '💰',
            self::Announcement => '📢',
            self::Roster, self::Registration, self::User => '👤',
            self::Judging => '🧑‍⚖️',
            self::Backup => '💾',
            self::Security, self::Authentication => '🔒',
            self::System => '⚙️',
            self::Event => '📅',
        };
    }
}
