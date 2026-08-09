<?php

namespace App\Services\Talent;

use App\Enums\TalentEventStatus;
use App\Models\TalentEvent;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for Talent Competition display status.
 *
 * Priority (highest first):
 * Archived → Results Published → Voting Paused
 * → Registration Open (until voting actually starts)
 * → Voting Open → Voting Closed → Registration Closed
 * → Scheduled → Draft
 */
class TalentCompetitionStatusResolver
{
    /**
     * @return array{key: string, label: string}
     */
    public function resolve(TalentEvent $event, ?Carbon $at = null): array
    {
        $at ??= now();

        if ($event->status === TalentEventStatus::Completed || $event->isArchived()) {
            return $this->status('archived', 'Archived');
        }

        if ($event->status === TalentEventStatus::ResultsPublished
            || $event->results_published_at !== null) {
            return $this->status('results_published', 'Results Published');
        }

        if ($event->is_paused) {
            return $this->status('voting_paused', 'Voting Paused');
        }

        // Registration takes priority until the voting window has actually started.
        if ($event->isRegistrationOpen($at) && ! $event->votingWindowHasStarted($at)) {
            return $this->status('registration_open', 'Registration Open');
        }

        if ($event->isAcceptingVotes($at)) {
            return $this->status('voting_open', 'Voting Open');
        }

        if ($event->isAfterVotingEnd($at)
            || ($event->status === TalentEventStatus::VotingOpen && $event->votingHasClosed())) {
            return $this->status('voting_closed', 'Voting Closed');
        }

        // Registration still open while voting has started (overlap) — show voting,
        // otherwise surface registration-closed between phases.
        if ($event->isRegistrationOpen($at)) {
            return $this->status('registration_open', 'Registration Open');
        }

        if ($this->registrationHasClosed($event, $at)) {
            return $this->status('registration_closed', 'Registration Closed');
        }

        if (! $event->published_to_students) {
            return $this->status('draft', 'Draft');
        }

        if ($event->isBeforeVotingStart($at)
            || ($event->registration_starts_at && $at->lt($event->registration_starts_at))) {
            return $this->status('scheduled', 'Scheduled');
        }

        return $this->status('draft', 'Draft');
    }

    public function key(TalentEvent $event, ?Carbon $at = null): string
    {
        return $this->resolve($event, $at)['key'];
    }

    public function label(TalentEvent $event, ?Carbon $at = null): string
    {
        return $this->resolve($event, $at)['label'];
    }

    protected function registrationHasClosed(TalentEvent $event, Carbon $at): bool
    {
        if ($event->registration_ends_at && $at->gt($event->registration_ends_at)) {
            return true;
        }

        $deadline = $event->submission_deadline;

        return $deadline !== null && $at->gt($deadline);
    }

    /**
     * @return array{key: string, label: string}
     */
    protected function status(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
        ];
    }
}
