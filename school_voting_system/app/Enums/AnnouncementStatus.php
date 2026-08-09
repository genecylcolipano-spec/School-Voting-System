<?php

namespace App\Enums;

enum AnnouncementStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Expired = 'expired';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
            self::Expired => 'Expired',
            self::Archived => 'Archived',
        };
    }

    /**
     * @return list<self>
     */
    public static function manualCases(): array
    {
        return [self::Draft, self::Published, self::Archived];
    }
}
