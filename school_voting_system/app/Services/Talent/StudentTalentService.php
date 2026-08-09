<?php

namespace App\Services\Talent;

use App\Enums\TalentEventStatus;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
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
        $events = TalentEvent::query()
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

    public function openPublishedCount(): int
    {
        return $this->activePhaseSummary()['voting_open'];
    }
}
