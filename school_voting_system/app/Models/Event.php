<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\HasEventImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
        'starts_at',
        'ends_at',
        'event_date',
        'venue',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => EventStatus::class,
            'image_variants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if ($event->starts_at && ! $event->ends_at) {
                $event->ends_at = $event->starts_at->copy()->endOfDay();
            }
        });
    }

    /**
     * Legacy alias for starts_at so mixed talent/school listings keep working.
     */
    protected function eventDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->starts_at,
            set: function (mixed $value): array {
                return [
                    'starts_at' => $value ? Carbon::parse($value) : null,
                ];
            },
        );
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

        if ($this->ends_at?->lt($now)) {
            return EventStatus::Completed;
        }

        if ($this->status === EventStatus::Ongoing) {
            return EventStatus::Ongoing;
        }

        if ($this->starts_at?->lte($now) && ($this->ends_at === null || $this->ends_at->gte($now))) {
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
        if (! $this->starts_at) {
            return 'TBA';
        }

        if (! $this->ends_at || $this->ends_at->equalTo($this->starts_at)) {
            return $this->starts_at->format('M d, Y · g:i A');
        }

        if ($this->starts_at->isSameDay($this->ends_at)) {
            return $this->starts_at->format('M d, Y · g:i A').' – '.$this->ends_at->format('g:i A');
        }

        return $this->starts_at->format('M d, Y · g:i A').' – '.$this->ends_at->format('M d, Y · g:i A');
    }

    public function scheduleDateLabel(): string
    {
        if (! $this->starts_at) {
            return '—';
        }

        if (! $this->ends_at || $this->starts_at->isSameDay($this->ends_at)) {
            return $this->starts_at->format('M d, Y');
        }

        if ($this->starts_at->isSameYear($this->ends_at) && $this->starts_at->isSameMonth($this->ends_at)) {
            return $this->starts_at->format('M d').' – '.$this->ends_at->format('d, Y');
        }

        return $this->starts_at->format('M d, Y').' – '.$this->ends_at->format('M d, Y');
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

        return $query
            ->visibleToCampus()
            ->orderByRaw(
                'case when starts_at <= ? and ends_at >= ? then 0 when starts_at > ? then 1 else 2 end',
                [$now, $now, $now]
            )
            ->orderByRaw('case when starts_at > ? then starts_at end asc', [$now])
            ->orderByRaw('case when ends_at < ? then starts_at end desc', [$now]);
    }

    /**
     * Persist schedule-driven status: past windows become Completed, current windows Ongoing.
     */
    public static function markOverdueAsCompleted(): int
    {
        $now = now();

        $completed = static::query()
            ->whereIn('status', [EventStatus::Scheduled, EventStatus::Ongoing])
            ->where('ends_at', '<', $now)
            ->update(['status' => EventStatus::Completed]);

        $ongoing = static::query()
            ->where('status', EventStatus::Scheduled)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->update(['status' => EventStatus::Ongoing]);

        return $completed + $ongoing;
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        static::markOverdueAsCompleted();

        return $query
            ->where('status', EventStatus::Scheduled)
            ->where('starts_at', '>=', now());
    }
}
