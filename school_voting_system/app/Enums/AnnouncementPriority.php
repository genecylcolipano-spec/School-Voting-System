<?php

namespace App\Enums;

enum AnnouncementPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Normal => 'Normal',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Low => 'border-slate-500/30 bg-slate-500/10 text-slate-300',
            self::Normal => 'border-blue-500/30 bg-blue-500/10 text-blue-200',
            self::High => 'border-orange-500/30 bg-orange-500/10 text-orange-200',
            self::Urgent => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
        };
    }

    public function sortWeight(): int
    {
        return match ($this) {
            self::Urgent => 4,
            self::High => 3,
            self::Normal => 2,
            self::Low => 1,
        };
    }
}
