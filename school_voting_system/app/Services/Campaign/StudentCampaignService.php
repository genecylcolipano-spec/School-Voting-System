<?php

namespace App\Services\Campaign;

use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Partylist;
use App\Models\User;
use App\Services\Election\StudentElectionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentCampaignService
{
    public function __construct(
        protected StudentElectionService $elections,
    ) {}

    public function publishedForStudent(int $limit = 4): Collection
    {
        return Partylist::query()
            ->visibleToStudents()
            ->with([
                'elections',
                'approvedPosters',
            ])
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function paginatedPublished(): LengthAwarePaginator
    {
        return Partylist::query()
            ->visibleToStudents()
            ->with([
                'elections',
                'approvedPosters',
            ])
            ->orderBy('name')
            ->paginate(12);
    }

    public function publishedCount(): int
    {
        return Partylist::query()->visibleToStudents()->count();
    }

    /**
     * Students may browse any Active campaign, regardless of whether an election
     * is currently running.
     */
    public function assertVisibleToStudents(Partylist $partylist): void
    {
        abort_unless($partylist->isActive(), 404);
    }

    /**
     * Pick the election that best drives the campaign's vote button: an election
     * in its active voting window first, then the soonest upcoming, then the
     * most recently concluded.
     */
    public function relevantElection(Partylist $partylist): ?Election
    {
        $partylist->loadMissing('elections');
        $elections = $partylist->elections;

        if ($elections->isEmpty()) {
            return null;
        }

        $now = now();

        $active = $elections->first(fn (Election $election) => $election->isInActiveVotingPeriod($now));
        if ($active) {
            return $active;
        }

        $upcoming = $elections
            ->filter(fn (Election $election) => $election->isBeforeVotingStart($now))
            ->sortBy('voting_starts_at')
            ->first();
        if ($upcoming) {
            return $upcoming;
        }

        return $elections
            ->sortByDesc(fn (Election $election) => $election->voting_ends_at ?? $election->created_at)
            ->first();
    }

    /**
     * Candidates that belong to this campaign AND the given election only.
     * Both partylist_id and election_id must match; the election must also be
     * linked to the campaign via the election_partylist pivot.
     */
    public function candidatesFor(Partylist $partylist, ?Election $election): Collection
    {
        if (! $election) {
            return collect();
        }

        $partylist->loadMissing('elections');

        if (! $partylist->elections->contains('id', $election->id)) {
            return collect();
        }

        return Candidate::query()
            ->where('partylist_id', $partylist->id)
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->with(['category', 'election'])
            ->get()
            ->sortBy(fn (Candidate $candidate) => [
                $candidate->category?->sort_order ?? 999,
                strtolower($candidate->category?->name ?? $candidate->position ?? ''),
                strtolower($candidate->display_name),
            ])
            ->values();
    }

    /**
     * Dynamic vote-button state derived from election status, voting window,
     * the student's vote record, and results-publication status.
     *
     * @return array{label: string, enabled: bool, url: ?string, message: ?string, state: string}
     */
    public function buttonStateFor(Partylist $partylist, ?User $student): array
    {
        $election = $this->relevantElection($partylist);

        if (! $election) {
            return $this->buttonState('No Election Available', 'no_election');
        }

        if ($election->status === ElectionStatus::Draft) {
            return $this->buttonState('Election Not Ready', 'draft');
        }

        $availability = $this->elections->votingAvailability($election, $student);

        return match ($availability['state']) {
            'open' => $availability['can_vote']
                ? $this->buttonState('Vote Now', 'open', enabled: true, url: route('student.voting.show', $election))
                : $this->buttonState('You Have Already Voted', 'voted', message: $this->votedConfirmationMessage($availability)),
            'voted' => $this->buttonState('You Have Already Voted', 'voted', message: $this->votedConfirmationMessage($availability)),
            'not_started' => $this->buttonState('Please wait until voting opens', 'not_started', message: $this->scheduleMessage($election)),
            'under_review' => $this->buttonState(
                'Results Under Review',
                'under_review',
                message: 'The administrator is currently reviewing and preparing the official election results.',
            ),
            'results_published' => $this->buttonState('View Official Results', 'results_published', enabled: true, url: route('student.results.election.show', $election)),
            default => $this->buttonState($availability['message'] ?? 'Voting Unavailable', $availability['state'] ?? 'unavailable'),
        };
    }

    /**
     * @return array{label: string, enabled: bool, url: ?string, message: ?string, state: string}
     */
    protected function buttonState(string $label, string $state, bool $enabled = false, ?string $url = null, ?string $message = null): array
    {
        return [
            'label' => $label,
            'state' => $state,
            'enabled' => $enabled,
            'url' => $url,
            'message' => $message,
        ];
    }

    protected function scheduleMessage(Election $election): ?string
    {
        if (! $election->voting_starts_at) {
            return null;
        }

        return 'Voting opens on '.$election->voting_starts_at->format('M j, Y · g:i A').'.';
    }

    /**
     * @param  array{title?: ?string, submessage?: ?string}  $availability
     */
    protected function votedConfirmationMessage(array $availability): string
    {
        return $availability['title'] ?? 'Your ballot has already been submitted.';
    }
}
