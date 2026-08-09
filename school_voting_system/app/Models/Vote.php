<?php

namespace App\Models;

use App\Exceptions\VoteIntegrityException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'election_category_id',
        'candidate_id',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vote $vote) {
            $vote->assertBallotIntegrity();
        });
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Cast a ballot inside a DB transaction. Relies on the unique index
     * (user_id, election_category_id) as a final race-condition guard.
     */
    public static function castBallot(User $voter, Candidate $candidate): self
    {
        return DB::transaction(function () use ($voter, $candidate) {
            $candidate->loadMissing(['election', 'category']);

            try {
                return static::create([
                    'user_id' => $voter->id,
                    'election_id' => $candidate->election_id,
                    'election_category_id' => $candidate->election_category_id,
                    'candidate_id' => $candidate->id,
                    'voted_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if (self::isDuplicateCategoryVote($exception)) {
                    throw new VoteIntegrityException('You have already voted in this category.', previous: $exception);
                }

                throw $exception;
            }
        });
    }

    protected static function isDuplicateCategoryVote(QueryException $exception): bool
    {
        $errorCode = $exception->errorInfo[1] ?? null;

        return in_array($errorCode, [1062, 19], true);
    }

    public function assertBallotIntegrity(): void
    {
        $this->syncDenormalizedKeysFromCandidate();

        $voter = $this->voter ?? User::query()->find($this->user_id);
        if (! $voter) {
            throw new VoteIntegrityException('Voter account not found.');
        }

        if (! $voter->canVote()) {
            throw new VoteIntegrityException('Only students are allowed to cast votes.');
        }

        $candidate = $this->candidate ?? Candidate::query()
            ->with(['election', 'category'])
            ->find($this->candidate_id);

        if (! $candidate) {
            throw new VoteIntegrityException('Selected candidate does not exist.');
        }

        if (! $candidate->is_active) {
            throw new VoteIntegrityException('Selected candidate is not active.');
        }

        if ((int) $candidate->election_id !== (int) $this->election_id) {
            throw new VoteIntegrityException('Candidate does not belong to this election.');
        }

        if ((int) $candidate->election_category_id !== (int) $this->election_category_id) {
            throw new VoteIntegrityException('Candidate does not belong to this election category.');
        }

        $election = $candidate->election ?? Election::query()->find($this->election_id);
        if (! $election) {
            throw new VoteIntegrityException('Election not found.');
        }

        if (! $election->isAcceptingVotes()) {
            throw new VoteIntegrityException('This election is not currently accepting votes.');
        }

        if ($voter->hasVotedInCategory($candidate->category)) {
            throw new VoteIntegrityException('You have already voted in this category.');
        }
    }

    protected function syncDenormalizedKeysFromCandidate(): void
    {
        if (! $this->candidate_id) {
            return;
        }

        $candidate = Candidate::query()->find($this->candidate_id);
        if (! $candidate) {
            return;
        }

        $this->election_id ??= $candidate->election_id;
        $this->election_category_id ??= $candidate->election_category_id;
    }
}
