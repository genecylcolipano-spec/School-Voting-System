<?php

namespace App\Services\Student;

use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\User;
use App\Services\Campaign\StudentCampaignService;
use App\Services\Talent\StudentTalentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class StudentOverviewService
{
    public const SHARED_TTL_SECONDS = 45;

    public function __construct(
        protected StudentTalentService $talentService,
        protected StudentCampaignService $campaignService,
        protected StudentBallotProgress $ballotProgress,
    ) {}

    /**
     * @return array{
     *     can_vote_now: bool,
     *     vote_now_url: string,
     *     vote_now_hint: ?string,
     *     cards: list<array<string, mixed>>
     * }
     */
    public function forStudent(User $student): array
    {
        $elections = $this->electionOverview($student);
        $talent = $this->talentOverview($student);
        $counts = $this->sharedCounts();
        $upcomingEvents = $counts['upcoming_events'];
        $openFundraisers = $counts['open_fundraisers'];
        $campaigns = $counts['campaigns'];
        $votingOpen = $counts['voting_open'] || $elections['count'] > 0;

        return [
            'can_vote_now' => $elections['can_vote_now'],
            'vote_now_url' => $elections['href'],
            'vote_now_hint' => $elections['hint'],
            'cards' => [
                $this->card(
                    key: 'elections',
                    title: 'Open Elections',
                    count: $elections['count'],
                    status: $elections['status'],
                    action: $elections['action'],
                    href: $elections['href'],
                    icon: 'vote',
                ),
                $this->card(
                    key: 'events',
                    title: 'School Events',
                    count: $upcomingEvents,
                    status: $upcomingEvents > 0 ? 'Upcoming now' : 'None upcoming',
                    action: 'View events →',
                    href: route('student.events.index'),
                    icon: 'events',
                ),
                $this->card(
                    key: 'talent',
                    title: 'Talent Competitions',
                    count: $talent['count'],
                    status: $talent['status'],
                    action: $talent['action'],
                    href: $talent['href'],
                    icon: 'talent',
                ),
                $this->card(
                    key: 'fundraising',
                    title: 'Fundraising',
                    count: $openFundraisers,
                    status: $openFundraisers > 0
                        ? $openFundraisers.' '.($openFundraisers === 1 ? 'drive open now' : 'drives open now')
                        : 'None accepting donations',
                    action: 'Support now →',
                    href: route('student.fundraising.index'),
                    icon: 'fundraising',
                ),
                $this->card(
                    key: 'campaigns',
                    title: 'Election Campaigns',
                    count: $campaigns,
                    status: match (true) {
                        $campaigns === 0 => 'No campaigns yet',
                        $votingOpen => 'Voting open',
                        default => 'Browse platforms',
                    },
                    action: 'View campaigns →',
                    href: route('student.campaigns.index'),
                    icon: 'campaigns',
                ),
            ],
        ];
    }

    /**
     * @return array{count: int, status: string, action: string, href: string, can_vote_now: bool, hint: ?string}
     */
    protected function electionOverview(User $student): array
    {
        $open = $this->openElections();
        $remaining = $this->ballotProgress->remainingElections($open, $student);
        $firstRemaining = $remaining->first();
        $indexUrl = route('student.voting.index');
        $count = $open->count();

        if ($firstRemaining) {
            $url = route('student.voting.show', $firstRemaining);

            return [
                'count' => $count,
                'status' => $count === 1 ? '1 open now' : $count.' open now',
                'action' => 'Vote now →',
                'href' => $url,
                'can_vote_now' => true,
                'hint' => null,
            ];
        }

        if ($count > 0) {
            return [
                'count' => $count,
                'status' => 'You have voted',
                'action' => 'View voting →',
                'href' => $indexUrl,
                'can_vote_now' => false,
                'hint' => 'You have already voted.',
            ];
        }

        return [
            'count' => 0,
            'status' => 'None open now',
            'action' => 'View voting →',
            'href' => $indexUrl,
            'can_vote_now' => false,
            'hint' => 'No active elections at the moment.',
        ];
    }

    /**
     * @return Collection<int, Election>
     */
    protected function openElections(): Collection
    {
        return Cache::remember('student.overview.open_elections', self::SHARED_TTL_SECONDS, function () {
            return Election::query()
                ->acceptingVotes()
                ->orderBy('voting_ends_at')
                ->get(['id', 'slug', 'voting_ends_at']);
        });
    }

    /**
     * @return array{upcoming_events: int, open_fundraisers: int, campaigns: int, voting_open: bool}
     */
    protected function sharedCounts(): array
    {
        return Cache::remember('student.overview.shared_counts', self::SHARED_TTL_SECONDS, function () {
            return [
                'upcoming_events' => Event::query()->upcoming()->count(),
                'open_fundraisers' => Fundraiser::query()->acceptingDonations()->count(),
                'campaigns' => $this->campaignService->publishedCount(),
                'voting_open' => Election::query()->acceptingVotes()->exists(),
            ];
        });
    }

    /**
     * @return array{count: int, status: string, action: string, href: string}
     */
    protected function talentOverview(User $student): array
    {
        $phase = $this->talentService->overviewForStudent($student);
        $count = $phase['total'];
        $listingUrl = $phase['registration_open'] > 0 && $phase['voting_open'] === 0
            ? route('student.talent-registration.index')
            : route('student.talent-voting.index');

        if ($phase['remaining_votes'] > 0) {
            return [
                'count' => $count,
                'status' => $count === 1 ? '1 open now' : $count.' open now',
                'action' => 'Vote now →',
                'href' => route('student.talent-voting.index'),
            ];
        }

        if ($phase['remaining_registrations'] > 0) {
            return [
                'count' => $count,
                'status' => $count === 1 ? '1 open now' : $count.' open now',
                'action' => 'Register now →',
                'href' => route('student.talent-registration.index'),
            ];
        }

        if ($count > 0) {
            return [
                'count' => $count,
                'status' => 'You have already participated',
                'action' => 'View competitions →',
                'href' => $listingUrl,
            ];
        }

        return [
            'count' => 0,
            'status' => 'None open now',
            'action' => 'View competitions →',
            'href' => route('student.talent-voting.index'),
        ];
    }

    /**
     * @return array{key: string, title: string, count: int, status: string, action: string, href: string, enabled: bool, icon: string}
     */
    protected function card(
        string $key,
        string $title,
        int $count,
        string $status,
        string $action,
        string $href,
        string $icon,
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'count' => $count,
            'status' => $status,
            'action' => $action,
            'href' => $href,
            'enabled' => true,
            'icon' => $icon,
        ];
    }
}
