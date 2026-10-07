<?php

namespace App\Enums;

use DateTimeInterface;
use Illuminate\Support\Carbon;

enum EventStatus: string
{
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Derive status from the event window. Cancelled is never overridden by dates.
     */
    public static function fromSchedule(mixed $startsAt, mixed $endsAt, self|string|null $current = null): self
    {
        $currentStatus = $current instanceof self
            ? $current
            : self::tryFrom((string) ($current ?? ''));

        if ($currentStatus === self::Cancelled) {
            return self::Cancelled;
        }

        $starts = self::parseScheduleDate($startsAt);
        $ends = self::parseScheduleDate($endsAt);
        $now = now();

        if ($ends?->lt($now)) {
            return self::Completed;
        }

        if ($starts?->lte($now) && ($ends === null || $ends->gte($now))) {
            return self::Ongoing;
        }

        return self::Scheduled;
    }

    protected static function parseScheduleDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::parse($value);
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    public function notifiesCampus(): bool
    {
        return $this === self::Scheduled || $this === self::Ongoing;
    }
}
