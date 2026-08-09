<?php

namespace App\Models;

use App\Exceptions\VoteIntegrityException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TalentEventVote extends Model
{
    protected $fillable = [
        'talent_event_id',
        'talent_event_entry_id',
        'user_id',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TalentEventEntry::class, 'talent_event_entry_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function castVote(User $voter, TalentEventEntry $entry): self
    {
        return DB::transaction(function () use ($voter, $entry) {
            $entry->loadMissing('talentEvent');

            if (! $voter->canVote()) {
                throw new VoteIntegrityException('Only eligible students can vote in talent events.');
            }

            if (! $entry->isApproved()) {
                throw new VoteIntegrityException('This entry is not approved for voting.');
            }

            $event = $entry->talentEvent;
            if (! $event || ! $event->isPublishedToStudents()) {
                throw new VoteIntegrityException('This talent event is not available to students.');
            }

            if (! $event->isAcceptingVotes()) {
                throw new VoteIntegrityException('This talent event is not currently accepting votes.');
            }

            try {
                return static::create([
                    'talent_event_id' => $event->id,
                    'talent_event_entry_id' => $entry->id,
                    'user_id' => $voter->id,
                    'voted_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if (in_array($exception->errorInfo[1] ?? null, [1062, 19], true)) {
                    throw new VoteIntegrityException('You have already voted in this talent event.', previous: $exception);
                }

                throw $exception;
            }
        });
    }
}
