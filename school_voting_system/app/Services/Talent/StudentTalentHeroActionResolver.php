<?php

namespace App\Services\Talent;

use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;

/**
 * Resolves the single competition-status CTA for the student talent details hero.
 *
 * Entry access belongs on My Entries / participant sections — never in the hero.
 */
class StudentTalentHeroActionResolver
{
    /**
     * @return array{
     *     phase: string,
     *     primary: array{label: string, href: ?string, disabled: bool, style: string}|null,
     *     secondary: null
     * }
     */
    public function resolve(
        TalentEvent $event,
        User $student,
        bool $hasVoted = false,
        ?TalentEventEntry $entry = null,
    ): array {
        $isParticipant = $entry !== null;
        $registrationOpen = $event->isRegistrationOpen();
        $votingOpen = $event->isAcceptingVotes();
        $resultsPublished = $event->hasPublishedResults();
        $votingClosed = $event->votingHasClosed() && ! $resultsPublished;

        $registerHref = route('student.talent-registration.register', $event);
        $resultsHref = route('student.results.talent.show', $event);
        $voteHref = '#candidates';

        if ($resultsPublished) {
            return $this->result('results_published', $this->action('View Results', $resultsHref));
        }

        if ($votingClosed) {
            return $this->result(
                'voting_closed',
                $this->action('Results Pending', null, disabled: true, style: 'disabled'),
            );
        }

        if ($votingOpen) {
            if ($hasVoted) {
                return $this->result(
                    'voting_open_voted',
                    $this->action('Vote Recorded', null, disabled: true, style: 'disabled'),
                );
            }

            return $this->result('voting_open', $this->action('Vote Now', $voteHref));
        }

        if ($registrationOpen) {
            if ($isParticipant) {
                // Competition is still open for registration, but this student already entered.
                // No hero CTA — entry review lives on My Entries.
                return $this->result('registration_open_participant', null);
            }

            return $this->result('registration_open', $this->action('Register Now', $registerHref));
        }

        return $this->result(
            'registration_closed',
            $this->action('Registration Closed', null, disabled: true, style: 'disabled'),
        );
    }

    /**
     * @param  array{label: string, href: ?string, disabled: bool, style: string}|null  $primary
     * @return array{phase: string, primary: array{label: string, href: ?string, disabled: bool, style: string}|null, secondary: null}
     */
    protected function result(string $phase, ?array $primary): array
    {
        return [
            'phase' => $phase,
            'primary' => $primary,
            'secondary' => null,
        ];
    }

    /**
     * @return array{label: string, href: ?string, disabled: bool, style: string}
     */
    protected function action(
        string $label,
        ?string $href,
        bool $disabled = false,
        string $style = 'primary',
    ): array {
        return [
            'label' => $label,
            'href' => $disabled ? null : $href,
            'disabled' => $disabled,
            'style' => $disabled ? 'disabled' : $style,
        ];
    }
}
