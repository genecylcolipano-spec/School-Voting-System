<?php

namespace App\Services\Talent;

use App\Enums\TalentEventStatus;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventEntryView;
use App\Models\TalentEventVote;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentTalentService
{
    public function publishedEventsForStudent(?User $student = null, int $limit = 6): Collection
    {
        $events = TalentEvent::query()
            ->publishedToStudents()
            ->with([
                'approvedEntries' => fn ($q) => $q->withCount('votes'),
            ])
            ->withCount(['votes', 'approvedEntries as entries_count'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return $this->hydrateStudentCardState($events, $student);
    }

    public function paginatedPublishedEvents(?User $student = null): LengthAwarePaginator
    {
        $events = TalentEvent::query()
            ->publishedToStudents()
            ->withCount('approvedEntries')
            ->orderByDesc('published_at')
            ->paginate(10);

        $events->setCollection(
            $this->hydrateStudentCardState($events->getCollection(), $student)
        );

        return $events;
    }

    /**
     * Attach student vote/entry context and derived card phase labels.
     *
     * @param  Collection<int, TalentEvent>  $events
     * @return Collection<int, TalentEvent>
     */
    public function hydrateStudentCardState(Collection $events, ?User $student = null): Collection
    {
        if ($events->isEmpty()) {
            return $events;
        }

        $votedEventIds = collect();
        $entryStatuses = collect();

        if ($student) {
            $eventIds = $events->pluck('id');

            $votedEventIds = TalentEventVote::query()
                ->where('user_id', $student->id)
                ->whereIn('talent_event_id', $eventIds)
                ->pluck('talent_event_id')
                ->flip();

            $entryStatuses = TalentEventEntry::query()
                ->where('user_id', $student->id)
                ->whereIn('talent_event_id', $eventIds)
                ->pluck('status', 'talent_event_id');
        }

        return $events->map(function (TalentEvent $event) use ($votedEventIds, $entryStatuses) {
            $hasVoted = $votedEventIds->has($event->id);
            $entryStatus = $entryStatuses->get($event->id);
            $phase = $event->studentCardPhase($hasVoted, $entryStatus);

            $event->setAttribute('student_has_voted', $hasVoted);
            $event->setAttribute('student_entry_status', $entryStatus);
            $event->setAttribute('student_phase_badge', $phase['badge']);
            $event->setAttribute('student_phase_cta', $phase['cta']);
            $event->setAttribute('student_phase_href', $phase['href']);
            $event->setAttribute('student_phase', $phase['phase']);

            return $event;
        });
    }

    public function assertVisibleToStudents(TalentEvent $event): void
    {
        abort_unless($event->published_to_students, 404);
    }

    public function hasVoted(User $student, TalentEvent $event): bool
    {
        return TalentEventVote::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $student->id)
            ->exists();
    }

    public function votedEntryId(User $student, TalentEvent $event): ?int
    {
        return TalentEventVote::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $student->id)
            ->value('talent_event_entry_id');
    }

    /**
     * Entry IDs this student has opened Watch Performance for in this competition.
     *
     * @return list<int>
     */
    public function watchedEntryIds(User $student, TalentEvent $event): array
    {
        return TalentEventEntryView::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $student->id)
            ->pluck('talent_event_entry_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function hasWatchedPerformance(User $student, TalentEventEntry $entry): bool
    {
        if (! $entry->hasVideo()) {
            return true;
        }

        return TalentEventEntryView::query()
            ->where('user_id', $student->id)
            ->where('talent_event_entry_id', $entry->id)
            ->exists();
    }

    public function recordWatch(User $student, TalentEventEntry $entry): TalentEventEntryView
    {
        $entry->loadMissing('talentEvent');

        return TalentEventEntryView::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'talent_event_entry_id' => $entry->id,
            ],
            [
                'talent_event_id' => $entry->talent_event_id,
                'watched_at' => now(),
            ],
        );
    }

    public function standings(TalentEvent $event): array
    {
        $entries = $event->approvedEntries()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->get();

        $totalVotes = (int) $entries->sum('votes_count');

        $rows = $entries->map(function ($entry) use ($totalVotes) {
            $votes = (int) $entry->votes_count;
            $percent = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0.0;

            return [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'grade_level' => $entry->grade_level,
                'section' => $entry->section,
                'votes' => $votes,
                'percent' => $percent,
            ];
        })->values()->all();

        return [
            'event_id' => $event->id,
            'total_votes' => $totalVotes,
            'updated_at' => now()->toIso8601String(),
            'entries' => $rows,
        ];
    }

    public function canViewStandings(User $student, TalentEvent $event): bool
    {
        return $event->hasPublishedResults();
    }

    /**
     * Published competitions currently in registration or voting (for dashboard cards).
     *
     * @return array{total: int, registration_open: int, voting_open: int}
     */
    public function activePhaseSummary(): array
    {
        return $this->summarizeOpenEvents($this->openPublishedEvents());
    }

    /**
     * @param  Collection<int, TalentEvent>  $events
     * @return array{total: int, registration_open: int, voting_open: int}
     */
    protected function summarizeOpenEvents(Collection $events): array
    {
        $registrationOpen = 0;
        $votingOpen = 0;

        foreach ($events as $event) {
            $key = $event->currentStatusKey();

            if ($key === 'registration_open') {
                $registrationOpen++;
            } elseif ($key === 'voting_open') {
                $votingOpen++;
            }
        }

        return [
            'total' => $registrationOpen + $votingOpen,
            'registration_open' => $registrationOpen,
            'voting_open' => $votingOpen,
        ];
    }

    /**
     * Open talent phases plus what this student still needs to do.
     *
     * @return array{
     *     total: int,
     *     registration_open: int,
     *     voting_open: int,
     *     remaining_votes: int,
     *     remaining_registrations: int
     * }
     */
    public function overviewForStudent(User $student): array
    {
        $events = $this->openPublishedEvents();
        $summary = $this->summarizeOpenEvents($events);

        $votingIds = $events
            ->filter(fn (TalentEvent $event) => $event->currentStatusKey() === 'voting_open')
            ->pluck('id');
        $registrationIds = $events
            ->filter(fn (TalentEvent $event) => $event->currentStatusKey() === 'registration_open')
            ->pluck('id');

        $votedIds = $votingIds->isEmpty()
            ? collect()
            : TalentEventVote::query()
                ->where('user_id', $student->id)
                ->whereIn('talent_event_id', $votingIds)
                ->pluck('talent_event_id')
                ->unique();

        $enteredIds = $registrationIds->isEmpty()
            ? collect()
            : TalentEventEntry::query()
                ->where('user_id', $student->id)
                ->whereIn('talent_event_id', $registrationIds)
                ->pluck('talent_event_id')
                ->unique();

        return [
            ...$summary,
            'remaining_votes' => $votingIds->diff($votedIds)->count(),
            'remaining_registrations' => $registrationIds->diff($enteredIds)->count(),
        ];
    }

    public function openPublishedCount(): int
    {
        return $this->activePhaseSummary()['voting_open'];
    }

    /**
     * @return Collection<int, TalentEvent>
     */
    protected function openPublishedEvents(): Collection
    {
        return TalentEvent::query()
            ->publishedToStudents()
            ->whereNull('results_published_at')
            ->where('status', '!=', TalentEventStatus::ResultsPublished)
            ->where('status', '!=', TalentEventStatus::Completed)
            ->where('is_paused', false)
            ->get([
                'id',
                'status',
                'published_to_students',
                'registration_starts_at',
                'registration_ends_at',
                'submission_deadline',
                'registration_method',
                'voting_starts_at',
                'voting_ends_at',
                'voting_method',
                'results_published_at',
                'is_paused',
            ]);
    }
}
