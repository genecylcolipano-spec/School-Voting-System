<?php

namespace App\Services\Election;

use App\Models\Election;
use App\Models\User;
use Illuminate\Support\Carbon;

class StudentElectionService
{
    public const WAITING_MESSAGE = 'Voting has not started yet.';

    public function canAccessBallot(Election $election, ?Carbon $at = null): bool
    {
        return $election->isAcceptingVotes($at);
    }

    /**
     * @return array{
     *     state: string,
     *     title: ?string,
     *     message: ?string,
     *     submessage: ?string,
     *     can_vote: bool,
     *     can_view_results: bool
     * }
     */
    public function votingAvailability(Election $election, ?User $student = null, ?Carbon $at = null): array
    {
        $at ??= now();

        if ($election->isInActiveVotingPeriod($at)) {
            if ($student && $election->hasStudentCompletedBallot($student)) {
                return [
                    'state' => 'voted',
                    'title' => 'Your vote has been successfully recorded.',
                    'message' => 'Voting is still ongoing.',
                    'submessage' => 'Official results will be published after administrator approval.',
                    'can_vote' => false,
                    'can_view_results' => false,
                ];
            }

            return [
                'state' => 'open',
                'title' => 'Voting is Open',
                'message' => 'The election is currently active.',
                'submessage' => 'Please cast your vote before voting closes.',
                'can_vote' => true,
                'can_view_results' => false,
            ];
        }

        if ($election->shouldShowOfficialResultsToStudents($at)) {
            return [
                'state' => 'results_published',
                'title' => 'Official Results Available',
                'message' => 'Congratulations!',
                'submessage' => 'The official election results have been published.',
                'can_vote' => false,
                'can_view_results' => true,
            ];
        }

        if ($election->isBeforeVotingStart($at)) {
            return [
                'state' => 'not_started',
                'title' => null,
                'message' => self::WAITING_MESSAGE,
                'submessage' => null,
                'can_vote' => false,
                'can_view_results' => false,
            ];
        }

        if ($election->isAwaitingResultsPublication($at)) {
            return [
                'state' => 'under_review',
                'title' => 'Under Administrator Review',
                'message' => 'Voting has ended.',
                'submessage' => 'Official results are currently under review.',
                'can_vote' => false,
                'can_view_results' => false,
            ];
        }

        if ($election->is_paused) {
            return [
                'state' => 'paused',
                'title' => null,
                'message' => 'Voting is temporarily paused. Please wait until voting resumes.',
                'submessage' => null,
                'can_vote' => false,
                'can_view_results' => false,
            ];
        }

        return [
            'state' => 'unavailable',
            'title' => null,
            'message' => 'Voting is not currently available.',
            'submessage' => null,
            'can_vote' => false,
            'can_view_results' => false,
        ];
    }

    public function ballotUnavailableMessage(Election $election, ?Carbon $at = null): string
    {
        $availability = $this->votingAvailability($election, null, $at);

        return trim(collect([
            $availability['message'],
            $availability['submessage'],
        ])->filter()->implode(' ')) ?: 'Voting is not currently available.';
    }
}
