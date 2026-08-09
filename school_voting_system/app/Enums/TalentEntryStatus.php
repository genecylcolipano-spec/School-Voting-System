<?php

namespace App\Enums;

enum TalentEntryStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Disqualified = 'disqualified';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
            self::Disqualified => 'Disqualified',
            self::Archived => 'Archived',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Approved => 'emerald',
            self::Pending => 'amber',
            self::Rejected, self::Disqualified => 'rose',
            self::Withdrawn, self::Archived => 'slate',
        };
    }

    /**
     * Statuses an entry can be moved to via admin review actions.
     *
     * @return array<int, self>
     */
    public static function reviewActionable(): array
    {
        return [self::Approved, self::Rejected, self::Withdrawn, self::Disqualified, self::Archived];
    }
}
