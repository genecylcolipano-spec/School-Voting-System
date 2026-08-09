<?php

namespace App\Services\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeAssignmentStatus;
use App\Enums\TalentJudgeRole;
use App\Enums\TalentJudgeScoreStatus;
use App\Enums\TalentVotingMethod;
use App\Enums\UserRole;
use App\Exceptions\JudgingIntegrityException;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventJudge;
use App\Models\TalentJudgeCriterionScore;
use App\Models\TalentJudgeScoreSheet;
use App\Models\TalentJudgingCriterion;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TalentJudgingService
{
    /**
     * @return list<array{name: string, max_points: int, sort_order: int}>
     */
    public static function defaultCriteria(): array
    {
        return [
            ['name' => 'Technique', 'max_points' => 25, 'sort_order' => 1],
            ['name' => 'Creativity', 'max_points' => 25, 'sort_order' => 2],
            ['name' => 'Stage Presence', 'max_points' => 25, 'sort_order' => 3],
            ['name' => 'Overall Impact', 'max_points' => 25, 'sort_order' => 4],
        ];
    }

    public function __construct(
        protected PortalNotificationService $notifications,
    ) {}

    public function ensureDefaultCriteria(TalentEvent $event): void
    {
        if ($event->judgingCriteria()->exists()) {
            return;
        }

        foreach (self::defaultCriteria() as $criterion) {
            $event->judgingCriteria()->create($criterion);
        }
    }

    public function assignJudge(
        TalentEvent $event,
        User $faculty,
        User $actor,
        TalentJudgeRole|string $role = TalentJudgeRole::Judge,
    ): TalentEventJudge {
        $role = $role instanceof TalentJudgeRole
            ? $role
            : TalentJudgeRole::from((string) $role);

        $this->assertAssignable($event, $faculty);

        // Admin-created competitions often default to student_only. Assigning a
        // judge enables faculty scoring without requiring a separate settings edit.
        $this->ensureCompetitionAcceptsJudges($event);

        $this->ensureDefaultCriteria($event);

        $existing = TalentEventJudge::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->first();

        if ($existing && $existing->isActive()) {
            throw ValidationException::withMessages([
                'talent_event_id' => 'This faculty member is already assigned to this competition.',
            ]);
        }

        if ($existing) {
            $existing->fill([
                'judge_role' => $role,
                'status' => TalentJudgeAssignmentStatus::Active,
                'assigned_by' => $actor->id,
                'assigned_at' => now(),
                'removal_reason' => null,
                'removed_at' => null,
                'removed_by' => null,
            ])->save();

            $assignment = $existing->fresh();
        } else {
            $assignment = TalentEventJudge::query()->create([
                'talent_event_id' => $event->id,
                'user_id' => $faculty->id,
                'assigned_by' => $actor->id,
                'judge_role' => $role,
                'status' => TalentJudgeAssignmentStatus::Active,
                'assigned_at' => now(),
            ]);
        }

        $this->notifications->facultyJudgeAssigned($faculty, $event, $actor, $assignment);
        $this->notifications->superAdminJudgeAssignmentCompleted($actor, $faculty, $event, $assignment);

        return $assignment;
    }

    /**
     * Competitions Super Admin may assign faculty judges to.
     *
     * @param  array<int>|null  $excludeEventIds
     * @return Builder<TalentEvent>
     */
    public function eligibleCompetitionsForAssignmentQuery(?array $excludeEventIds = null): Builder
    {
        return TalentEvent::query()
            ->whereNull('deleted_at')
            ->where('published_to_students', true)
            ->whereIn('status', [
                TalentEventStatus::Scheduled,
                TalentEventStatus::EntriesOpen,
                TalentEventStatus::VotingOpen,
            ])
            ->when(
                ! empty($excludeEventIds),
                fn (Builder $query) => $query->whereNotIn('id', $excludeEventIds)
            )
            ->orderBy('title');
    }

    /**
     * Enable judge scoring on competitions created as student-only.
     */
    public function ensureCompetitionAcceptsJudges(TalentEvent $event): void
    {
        if ($event->requiresJudges()) {
            return;
        }

        $event->forceFill([
            'voting_method' => TalentVotingMethod::JudgesAndStudents,
            'judge_percentage' => $event->judge_percentage ?? 50,
            'student_vote_percentage' => $event->student_vote_percentage ?? 50,
        ])->save();

        $event->refresh();
    }

    public function updateJudgeRole(
        TalentEvent $event,
        User $faculty,
        TalentJudgeRole|string $role,
        User $actor,
    ): TalentEventJudge {
        $role = $role instanceof TalentJudgeRole
            ? $role
            : TalentJudgeRole::from((string) $role);

        $assignment = TalentEventJudge::query()
            ->active()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->firstOrFail();

        $previous = $assignment->judge_role;
        $assignment->update(['judge_role' => $role]);

        if ($previous !== $role) {
            $this->notifications->facultyJudgeRoleUpdated($faculty, $event, $assignment, $actor);
        }

        return $assignment->fresh();
    }

    public function removeJudge(
        TalentEvent $event,
        User $faculty,
        ?User $actor = null,
        ?string $reason = null,
    ): void {
        $assignment = TalentEventJudge::query()
            ->active()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->first();

        if (! $assignment) {
            return;
        }

        $assignment->update([
            'status' => TalentJudgeAssignmentStatus::Removed,
            'removal_reason' => $reason ? trim($reason) : null,
            'removed_at' => now(),
            'removed_by' => $actor?->id,
        ]);

        $this->notifications->facultyJudgeRemoved($faculty, $event, $actor, $assignment->fresh());
    }

    /**
     * @throws ValidationException
     */
    public function assertAssignable(TalentEvent $event, User $faculty): void
    {
        if (! $faculty->isFaculty()) {
            throw ValidationException::withMessages([
                'user_id' => 'Only Faculty accounts can be assigned as judges.',
            ]);
        }

        if (! $faculty->is_active || $faculty->archived_at !== null) {
            throw ValidationException::withMessages([
                'user_id' => 'Only active faculty accounts can be assigned as judges.',
            ]);
        }

        if (! $this->facultyHasRegisteredPasskey($faculty)) {
            throw ValidationException::withMessages([
                'user_id' => 'Faculty must have a registered Passkey before being assigned as a judge.',
            ]);
        }

        if (! $this->isCompetitionAssignable($event)) {
            throw ValidationException::withMessages([
                'talent_event_id' => 'This competition is not eligible for judge assignment. It must be published, active, and not archived or completed.',
            ]);
        }
    }

    public function facultyHasRegisteredPasskey(User $faculty): bool
    {
        return $faculty->passkeys()->exists();
    }

    public function isCompetitionAssignable(TalentEvent $event): bool
    {
        if ($event->trashed() || ! $event->isPublishedToStudents()) {
            return false;
        }

        return in_array($event->status, [
            TalentEventStatus::Scheduled,
            TalentEventStatus::EntriesOpen,
            TalentEventStatus::VotingOpen,
        ], true);
    }

    /**
     * @return Builder<TalentEvent>
     */
    public function assignedCompetitionsQuery(User $faculty): Builder
    {
        return TalentEvent::query()
            ->whereHas('judges', fn (Builder $query) => $query
                ->where('user_id', $faculty->id)
                ->where('status', TalentJudgeAssignmentStatus::Active))
            ->orderByDesc('voting_starts_at')
            ->orderByDesc('event_date');
    }

    public function assignmentFor(User $faculty, TalentEvent $event): ?TalentEventJudge
    {
        return TalentEventJudge::query()
            ->active()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->first();
    }

    public function assertAssigned(User $faculty, TalentEvent $event): void
    {
        abort_unless(
            (bool) $this->assignmentFor($faculty, $event),
            403,
            'You are not assigned to judge this competition.',
        );
    }

    public function judgeableEntries(TalentEvent $event): Collection
    {
        return $event->approvedEntries()
            ->orderBy('display_name')
            ->get();
    }

    public function scoreSheetFor(User $faculty, TalentEvent $event, TalentEventEntry $entry): ?TalentJudgeScoreSheet
    {
        return TalentJudgeScoreSheet::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->where('talent_event_entry_id', $entry->id)
            ->with('criterionScores')
            ->first();
    }

    /**
     * @param  array<int|string, mixed>  $scores  criterion_id => points
     */
    public function saveDraft(
        User $faculty,
        TalentEvent $event,
        TalentEventEntry $entry,
        array $scores,
        ?string $notes = null,
    ): TalentJudgeScoreSheet {
        return $this->persistScores($faculty, $event, $entry, $scores, $notes, submit: false);
    }

    /**
     * @param  array<int|string, mixed>  $scores
     */
    public function submitScores(
        User $faculty,
        TalentEvent $event,
        TalentEventEntry $entry,
        array $scores,
        ?string $notes = null,
    ): TalentJudgeScoreSheet {
        return $this->persistScores($faculty, $event, $entry, $scores, $notes, submit: true);
    }

    /**
     * @param  array<int|string, mixed>  $scores
     */
    protected function persistScores(
        User $faculty,
        TalentEvent $event,
        TalentEventEntry $entry,
        array $scores,
        ?string $notes,
        bool $submit,
    ): TalentJudgeScoreSheet {
        $this->assertAssigned($faculty, $event);

        if ((int) $entry->talent_event_id !== (int) $event->id) {
            throw new JudgingIntegrityException('This performance does not belong to the competition.');
        }

        if (! $entry->isApproved()) {
            throw new JudgingIntegrityException('Only approved performances can be scored.');
        }

        if (! $event->isAcceptingJudgeScores() && $submit) {
            throw new JudgingIntegrityException('Judging is not open for this competition right now.');
        }

        $criteria = $event->judgingCriteria()->orderBy('sort_order')->get();
        if ($criteria->isEmpty()) {
            $this->ensureDefaultCriteria($event);
            $criteria = $event->judgingCriteria()->orderBy('sort_order')->get();
        }

        $sheet = TalentJudgeScoreSheet::query()->firstOrNew([
            'talent_event_id' => $event->id,
            'user_id' => $faculty->id,
            'talent_event_entry_id' => $entry->id,
        ]);

        if ($sheet->exists && $sheet->isLocked()) {
            throw new JudgingIntegrityException('This score sheet has already been submitted and cannot be changed.');
        }

        if (! $event->isAcceptingJudgeScores() && ! $sheet->exists) {
            throw new JudgingIntegrityException('Judging is not open for this competition right now.');
        }

        $normalized = [];
        $total = 0.0;

        foreach ($criteria as $criterion) {
            $raw = $scores[$criterion->id] ?? $scores[(string) $criterion->id] ?? null;
            if ($raw === null || $raw === '') {
                throw new JudgingIntegrityException("Please score \"{$criterion->name}\".");
            }

            if (! is_numeric($raw)) {
                throw new JudgingIntegrityException("Invalid score for \"{$criterion->name}\".");
            }

            $points = round((float) $raw, 2);
            if ($points < 0 || $points > $criterion->max_points) {
                throw new JudgingIntegrityException(
                    "\"{$criterion->name}\" must be between 0 and {$criterion->max_points}."
                );
            }

            $normalized[$criterion->id] = $points;
            $total += $points;
        }

        return DB::transaction(function () use ($sheet, $normalized, $total, $notes, $submit, $faculty, $event, $entry) {
            $sheet->fill([
                'total_score' => round($total, 2),
                'notes' => $notes,
                'status' => $submit ? TalentJudgeScoreStatus::Submitted : TalentJudgeScoreStatus::Draft,
                'submitted_at' => $submit ? now() : null,
            ]);
            $sheet->talent_event_id = $event->id;
            $sheet->user_id = $faculty->id;
            $sheet->talent_event_entry_id = $entry->id;
            $sheet->save();

            foreach ($normalized as $criterionId => $points) {
                TalentJudgeCriterionScore::query()->updateOrCreate(
                    [
                        'score_sheet_id' => $sheet->id,
                        'criterion_id' => $criterionId,
                    ],
                    ['points' => $points],
                );
            }

            $fresh = $sheet->fresh(['criterionScores', 'entry']);

            if ($submit) {
                $this->notifications->facultyScoreSubmitted($faculty, $event);
            }

            return $fresh;
        });
    }

    /**
     * @return Collection<int, User>
     */
    public function availableFaculty(TalentEvent $event): Collection
    {
        $assignedIds = $event->judges()->pluck('user_id');

        return User::query()
            ->where('role', UserRole::Faculty)
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereNotIn('id', $assignedIds)
            ->whereHas('passkeys')
            ->orderBy('name')
            ->get();
    }

    /**
     * Progress for a faculty member on one competition.
     *
     * @return array{approved: int, drafted: int, submitted: int, remaining: int, percent: int, judging_status: string}
     */
    public function progressFor(User $faculty, TalentEvent $event): array
    {
        $approved = $event->approvedEntries()->count();
        $sheets = TalentJudgeScoreSheet::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $faculty->id)
            ->get();

        $submitted = $sheets->where('status', TalentJudgeScoreStatus::Submitted)->count();
        $drafted = $sheets->where('status', TalentJudgeScoreStatus::Draft)->count();
        $remaining = max(0, $approved - $submitted);
        $percent = $approved > 0 ? (int) round(($submitted / $approved) * 100) : 0;

        $judgingStatus = match (true) {
            $approved === 0 => 'Awaiting Participants',
            $submitted >= $approved && $approved > 0 => 'Complete',
            $submitted > 0 || $drafted > 0 => 'In Progress',
            default => 'Not Started',
        };

        return [
            'approved' => $approved,
            'drafted' => $drafted,
            'submitted' => $submitted,
            'remaining' => $remaining,
            'percent' => $percent,
            'judging_status' => $judgingStatus,
        ];
    }

    /**
     * Aggregated submitted-score summary rows for the faculty portal.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function submittedSummariesFor(User $faculty): Collection
    {
        $assignments = TalentEventJudge::query()
            ->active()
            ->where('user_id', $faculty->id)
            ->with('talentEvent')
            ->get();

        return $assignments->map(function (TalentEventJudge $assignment) use ($faculty) {
            $event = $assignment->talentEvent;
            if (! $event) {
                return null;
            }

            $progress = $this->progressFor($faculty, $event);
            $lastSubmitted = TalentJudgeScoreSheet::query()
                ->where('talent_event_id', $event->id)
                ->where('user_id', $faculty->id)
                ->where('status', TalentJudgeScoreStatus::Submitted)
                ->orderByDesc('submitted_at')
                ->value('submitted_at');

            if ($progress['submitted'] === 0) {
                return null;
            }

            return [
                'assignment' => $assignment,
                'competition' => $event,
                'judge_role' => $assignment->roleLabel(),
                'participants_judged' => $progress['submitted'],
                'participants_total' => $progress['approved'],
                'completion_percent' => $progress['percent'],
                'submission_date' => $lastSubmitted,
                'status' => $progress['judging_status'],
            ];
        })->filter()->values();
    }
}
