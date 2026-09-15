<?php

namespace App\Services\Faculty;

use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Services\Student\StudentUpcomingActivitiesService;

class FacultyUpcomingActivitiesService extends StudentUpcomingActivitiesService
{
    /**
     * @param  array{
     *     state: string,
     *     title: ?string,
     *     message: ?string,
     *     submessage: ?string,
     *     can_vote: bool,
     *     can_view_results: bool
     * }  $availability
     * @return array{status_key: string, status_label: string, action_label: string, action_url: ?string, action_disabled: bool}|null
     */
    protected function mapElectionAction(Election $election, array $availability): ?array
    {
        $mapped = parent::mapElectionAction($election, $availability);

        if ($mapped === null) {
            return null;
        }

        if ($availability['state'] === 'results_published') {
            return [
                'status_key' => $mapped['status_key'],
                'status_label' => $mapped['status_label'],
                'action_label' => 'View Results',
                'action_url' => route('faculty.results.election.show', $election),
                'action_disabled' => false,
            ];
        }

        if (! $election->isVisibleToCampus()) {
            return null;
        }

        return [
            'status_key' => $mapped['status_key'],
            'status_label' => $mapped['status_label'],
            'action_label' => 'View details',
            'action_url' => route('faculty.elections.show', $election),
            'action_disabled' => false,
        ];
    }

    /**
     * @return array{status_key: string, status_label: string, action_label: string, action_url: string}
     */
    protected function mapSchoolEventAction(Event $event): array
    {
        return [
            'status_key' => $event->campusStatusKey(),
            'status_label' => $event->campusStatusLabel(),
            'action_label' => 'View details',
            'action_url' => route('faculty.events.show', $event),
        ];
    }

    /**
     * @param  array{key: string, label: string}  $phase
     * @return array{status_key: string, status_label: string, action_label: string, action_url: ?string, action_disabled: bool}|null
     */
    protected function mapTalentAction(TalentEvent $talent, array $phase): ?array
    {
        $mapped = parent::mapTalentAction($talent, $phase);

        if ($mapped === null) {
            return null;
        }

        if ($talent->hasPublishedResults()) {
            return [
                'status_key' => $mapped['status_key'],
                'status_label' => $mapped['status_label'],
                'action_label' => 'View Results',
                'action_url' => route('faculty.results.talent.show', $talent),
                'action_disabled' => false,
            ];
        }

        return [
            'status_key' => $mapped['status_key'],
            'status_label' => $mapped['status_label'],
            'action_label' => 'View details',
            'action_url' => route('faculty.talent.show', $talent),
            'action_disabled' => false,
        ];
    }

    protected function fundraiserShowUrl(Fundraiser $fundraiser): string
    {
        return route('faculty.fundraising.show', $fundraiser);
    }

    protected function fundraiserActionLabel(bool $accepting): string
    {
        return 'View details';
    }
}
