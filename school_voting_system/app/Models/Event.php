<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\HasEventImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasEventImage;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'image_variants',
        'event_date',
        'venue',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'status' => EventStatus::class,
            'image_variants' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayStatus(): EventStatus
    {
        if ($this->status === EventStatus::Cancelled) {
            return EventStatus::Cancelled;
        }

        if ($this->status === EventStatus::Completed) {
            return EventStatus::Completed;
        }

        $now = now();
        $startOfToday = $now->copy()->startOfDay();

        if ($this->event_date?->lt($startOfToday)) {
            return EventStatus::Completed;
        }

        if ($this->status === EventStatus::Ongoing) {
            return EventStatus::Ongoing;
        }

        if ($this->event_date?->lte($now)) {
            return EventStatus::Ongoing;
        }

        return $this->status ?? EventStatus::Scheduled;
    }

    public function displayStatusLabel(): string
    {
        return $this->displayStatus()->label();
    }

    public function scheduleLabel(): string
    {
        return $this->event_date?->format('M d, Y · g:i A') ?? 'TBA';
    }

    public function campusStatusKey(): string
    {
        return match ($this->displayStatus()) {
            EventStatus::Cancelled => 'cancelled',
            EventStatus::Completed => 'completed',
            EventStatus::Ongoing => 'ongoing',
            default => 'upcoming',
        };
    }

    public function campusStatusLabel(): string
    {
        return match ($this->campusStatusKey()) {
            'completed' => 'Completed',
            'ongoing' => 'Ongoing',
            'cancelled' => 'Cancelled',
            default => 'Upcoming',
        };
    }

    public function isVisibleToCampus(): bool
    {
        return $this->status !== EventStatus::Cancelled;
    }

    public function scopeVisibleToCampus(Builder $query): Builder
    {
        return $query->where('status', '!=', EventStatus::Cancelled);
    }

    public function scopeCampusListing(Builder $query): Builder
    {
        $now = now();
        $startOfToday = $now->copy()->startOfDay();

        return $query
            ->visibleToCampus()
            ->orderByRaw(
                'case when event_date >= ? and event_date <= ? then 0 when event_date > ? then 1 else 2 end',
                [$startOfToday, $now, $now]
            )
            ->orderByRaw('case when event_date > ? then event_date end asc', [$now])
            ->orderByRaw('case when event_date <= ? then event_date end desc', [$now]);
    }

    /**
     * Persist schedule-driven status: past days become Completed, started-today become Ongoing.
     */
    public static function markOverdueAsCompleted(): int
    {
        $now = now();
        $startOfToday = $now->copy()->startOfDay();

        $completed = static::query()
            ->whereIn('status', [EventStatus::Scheduled, EventStatus::Ongoing])
            ->where('event_date', '<', $startOfToday)
            ->update(['status' => EventStatus::Completed]);

        $ongoing = static::query()
            ->where('status', EventStatus::Scheduled)
            ->where('event_date', '<=', $now)
            ->where('event_date', '>=', $startOfToday)
            ->update(['status' => EventStatus::Ongoing]);

        return $completed + $ongoing;
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        static::markOverdueAsCompleted();

        return $query
            ->where('status', EventStatus::Scheduled)
            ->where('event_date', '>=', now());
    }
}
