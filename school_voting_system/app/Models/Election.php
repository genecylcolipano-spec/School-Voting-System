<?php

namespace App\Models;

use App\Enums\ElectionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Election extends Model
{
    /** @use HasFactory<\Database\Factories\ElectionFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'voting_starts_at',
        'voting_ends_at',
        'status',
        'created_by',
        'is_paused',
        'results_locked',
        'integrity_hash',
        'public_results_published',
        'results_published_at',
        'results_published_by',
        'annulled_at',
        'rerun_parent_id',
        'scheduled_open_at',
        'scheduled_close_at',
    ];

    protected function casts(): array
    {
        return [
            'voting_starts_at' => 'datetime',
            'voting_ends_at' => 'datetime',
            'scheduled_open_at' => 'datetime',
            'scheduled_close_at' => 'datetime',
            'annulled_at' => 'datetime',
            'results_published_at' => 'datetime',
            'status' => ElectionStatus::class,
            'is_paused' => 'boolean',
            'results_locked' => 'boolean',
            'public_results_published' => 'boolean',
        ];
    }

    public function rerunParent(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'rerun_parent_id');
    }

    public function reruns(): HasMany
    {
        return $this->hasMany(Election::class, 'rerun_parent_id');
    }

    public function computeIntegrityHash(): string
    {
        $payload = $this->votes()
            ->orderBy('id')
            ->get(['id', 'user_id', 'candidate_id', 'voted_at'])
            ->toJson();

        return hash('sha256', $payload);
    }

    public function refreshIntegrityHash(): string
    {
        $hash = $this->computeIntegrityHash();
        $this->forceFill(['integrity_hash' => $hash])->save();

        return $hash;
    }

    public function integrityHashMatches(): bool
    {
        if (! $this->integrity_hash) {
            return false;
        }

        return hash_equals($this->integrity_hash, $this->computeIntegrityHash());
    }

    public function ballotSubmissions(): HasMany
    {
        return $this->hasMany(BallotSubmission::class);
    }

    public function eligibleVoterCount(): int
    {
        return User::query()
            ->where('role', \App\Enums\UserRole::Student)
            ->where('is_active', true)
            ->where('student_status', \App\Enums\StudentStatus::Enrolled)
            ->count();
    }

    public function turnoutPercent(): float
    {
        $eligible = $this->eligibleVoterCount();

        if ($eligible === 0) {
            return 0.0;
        }

        $voted = $this->votes()->distinct('user_id')->count('user_id');

        return round(($voted / $eligible) * 100, 1);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resultsPublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'results_published_by');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ElectionCategory::class)->orderBy('sort_order');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function partylists(): BelongsToMany
    {
        return $this->belongsToMany(Partylist::class, 'election_partylist')->withTimestamps();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function activeCandidates(): HasMany
    {
        return $this->candidates()->where('is_active', true);
    }

    /**
     * Positions with at least one active candidate (required for a complete ballot).
     *
     * @return list<int>
     */
    public function votableCategoryIds(): array
    {
        return $this->categories()
            ->whereHas('candidates', fn ($query) => $query->where('is_active', true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isInActiveVotingPeriod(?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->status?->isOpenForVoting() || $this->is_paused || $this->annulled_at) {
            return false;
        }

        if ($this->voting_starts_at && $at->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at && $at->gt($this->voting_ends_at)) {
            return false;
        }

        return true;
    }

    public function isAcceptingVotes(?Carbon $at = null): bool
    {
        return $this->isInActiveVotingPeriod($at);
    }

    public function shouldShowOfficialResultsToStudents(?Carbon $at = null): bool
    {
        if (! $this->public_results_published) {
            return false;
        }

        return ! $this->isInActiveVotingPeriod($at);
    }

    public function isAwaitingResultsPublication(?Carbon $at = null): bool
    {
        if ($this->public_results_published) {
            return false;
        }

        if ($this->isInActiveVotingPeriod($at)) {
            return false;
        }

        if ($this->isBeforeVotingStart($at)) {
            return false;
        }

        return $this->isAfterVotingEnd($at)
            || in_array($this->status, [ElectionStatus::Closed, ElectionStatus::Archived], true);
    }

    public function hasStudentCompletedBallot(User $user): bool
    {
        $categoryIds = $this->categories()
            ->whereHas('candidates', fn ($query) => $query->where('is_active', true))
            ->pluck('id');

        if ($categoryIds->isEmpty()) {
            return $user->votes()->where('election_id', $this->id)->exists();
        }

        $votedCategories = $user->votes()
            ->where('election_id', $this->id)
            ->whereIn('election_category_id', $categoryIds)
            ->distinct('election_category_id')
            ->count('election_category_id');

        return $votedCategories >= $categoryIds->count();
    }

    public function isAfterVotingEnd(?Carbon $at = null): bool
    {
        $at ??= now();

        return (bool) ($this->voting_ends_at && $at->gt($this->voting_ends_at));
    }

    public function isBeforeVotingStart(?Carbon $at = null): bool
    {
        $at ??= now();

        return (bool) ($this->voting_starts_at && $at->lt($this->voting_starts_at));
    }

    public function scopeAcceptingVotes(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where('status', ElectionStatus::Active)
            ->where('is_paused', false)
            ->whereNull('annulled_at')
            ->where(function (Builder $query) use ($at) {
                $query->whereNull('voting_starts_at')
                    ->orWhere('voting_starts_at', '<=', $at);
            })
            ->where(function (Builder $query) use ($at) {
                $query->whereNull('voting_ends_at')
                    ->orWhere('voting_ends_at', '>=', $at);
            });
    }

    /**
     * @return array{
     *     label: string,
     *     remaining: string,
     *     hours: int,
     *     minutes: int,
     *     phase: string,
     *     target_at_iso: string,
     *     is_closed: bool,
     *     ends_at_iso: ?string,
     *     starts_at_iso: ?string
     * }|null
     */
    public function countdownSnapshot(?Carbon $at = null): ?array
    {
        $at ??= now();

        if (! $this->voting_ends_at && ! $this->voting_starts_at) {
            return null;
        }

        if ($this->voting_starts_at && $at->lt($this->voting_starts_at)) {
            return $this->buildCountdown(
                Carbon::parse($this->voting_starts_at),
                'Voting Starts In',
                'before_start',
            );
        }

        if ($this->voting_ends_at) {
            $ends = Carbon::parse($this->voting_ends_at);

            if ($at->gte($ends)) {
                return [
                    'label' => 'Voting Closed',
                    'remaining' => '00 Hours 00 Minutes',
                    'hours' => 0,
                    'minutes' => 0,
                    'phase' => 'ended',
                    'target_at_iso' => $ends->toIso8601String(),
                    'is_closed' => true,
                    'ends_at_iso' => $ends->toIso8601String(),
                    'starts_at_iso' => $this->voting_starts_at?->toIso8601String(),
                ];
            }

            return $this->buildCountdown($ends, 'Voting Ends In', 'active');
        }

        return null;
    }

    /**
     * @return array{
     *     label: string,
     *     remaining: string,
     *     hours: int,
     *     minutes: int,
     *     phase: string,
     *     target_at_iso: string,
     *     is_closed: bool,
     *     ends_at_iso: ?string,
     *     starts_at_iso: ?string
     * }
     */
    protected function buildCountdown(Carbon $target, string $label, string $phase): array
    {
        $diff = now()->diff($target);
        $hours = ($diff->days * 24) + $diff->h;
        $minutes = $diff->i;

        return [
            'label' => $label,
            'remaining' => sprintf('%02d Hours %02d Minutes', $hours, $minutes),
            'hours' => $hours,
            'minutes' => $minutes,
            'phase' => $phase,
            'target_at_iso' => $target->toIso8601String(),
            'is_closed' => false,
            'ends_at_iso' => $this->voting_ends_at?->toIso8601String(),
            'starts_at_iso' => $this->voting_starts_at?->toIso8601String(),
        ];
    }
}
