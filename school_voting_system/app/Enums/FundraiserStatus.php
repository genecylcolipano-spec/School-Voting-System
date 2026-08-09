<?php

namespace App\Enums;

enum FundraiserStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case GoalReached = 'goal_reached';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::GoalReached => 'Goal Reached',
            self::Cancelled => 'Cancelled',
            self::Archived => 'Archived',
        };
    }

    public function acceptsDonations(): bool
    {
        return in_array($this, [self::Active, self::GoalReached], true);
    }

    /**
     * Statuses an administrator may set manually (auto statuses are computed).
     *
     * @return list<self>
     */
    public static function manualCases(): array
    {
        return [
            self::Draft,
            self::Active,
            self::Cancelled,
            self::Archived,
        ];
    }
}
