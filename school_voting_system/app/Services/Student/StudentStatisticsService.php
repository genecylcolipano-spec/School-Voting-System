<?php

namespace App\Services\Student;

use App\Enums\ElectionStatus;
use App\Models\Donation;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentStatisticsService
{
    /**
     * @return array<string, mixed>
     */
    public function forStudent(User $student): array
    {
        $student->loadCount([
            'passkeys',
            'votes',
            'donations' => fn ($query) => $query->paid(),
        ]);

        $joinedElectionIds = Vote::query()
            ->where('user_id', $student->id)
            ->distinct()
            ->pluck('election_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $joinedCompetitionIds = $this->participatingEntriesQuery($student)
            ->distinct()
            ->pluck('talent_event_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $supportedFundraiserIds = Donation::query()
            ->paid()
            ->where('user_id', $student->id)
            ->distinct()
            ->pluck('fundraiser_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $electionsJoined = $joinedElectionIds->count();
        $competitionsJoined = $joinedCompetitionIds->count();
        $fundraisersSupported = $supportedFundraiserIds->count();
        $totalDonated = (float) $student->donations()->paid()->sum('amount');

        $eligibleElections = $this->eligibleElectionCount($student, $joinedElectionIds);
        $eligibleCompetitions = $this->eligibleCompetitionCount($student, $joinedCompetitionIds);
        $visibleFundraisers = $this->relevantFundraiserCount($supportedFundraiserIds);

        $openCategoryIds = ElectionCategory::query()
            ->whereHas('election', fn ($query) => $query->acceptingVotes())
            ->pluck('id');
        $openCategoryCount = $openCategoryIds->count();
        $votesInOpenElections = $openCategoryCount > 0
            ? $student->votes()->whereIn('election_category_id', $openCategoryIds)->count()
            : 0;

        $lifetimeVotingPercent = $this->percent($electionsJoined, $eligibleElections);

        $lastVote = Vote::query()
            ->with(['election:id,title,slug'])
            ->where('user_id', $student->id)
            ->orderByDesc('voted_at')
            ->first();

        $lastCompetition = $this->participatingEntriesQuery($student)
            ->with(['talentEvent:id,title,slug'])
            ->orderByDesc('created_at')
            ->first();

        $lastDonation = Donation::query()
            ->paid()
            ->with(['fundraiser:id,title,slug'])
            ->where('user_id', $student->id)
            ->orderByDesc('donated_at')
            ->first();

        $recentDonations = Donation::query()
            ->paid()
            ->with(['fundraiser:id,title,slug'])
            ->where('user_id', $student->id)
            ->orderByDesc('donated_at')
            ->limit(5)
            ->get();

        $totalActivities = $electionsJoined + $competitionsJoined + $fundraisersSupported;

        return [
            'overview' => [
                'votes_cast' => (int) $student->votes_count,
                'elections_joined' => $electionsJoined,
                'competitions_joined' => $competitionsJoined,
                'fundraisers_supported' => $fundraisersSupported,
            ],
            'activitySummary' => [
                'recent_vote' => $lastVote?->election?->title,
                'last_event' => null,
                'last_competition' => $lastCompetition?->talentEvent?->title,
                'last_donation' => $lastDonation?->fundraiser?->title,
                'passkeys' => (int) $student->passkeys_count,
                'member_since' => $student->created_at,
            ],
            'votingAnalytics' => [
                'has_open_elections' => $openCategoryCount > 0,
                'has_history' => $electionsJoined > 0 || (int) $student->votes_count > 0,
                'elections_joined' => $electionsJoined,
                'eligible_elections' => $eligibleElections,
                'lifetime_percent' => $lifetimeVotingPercent,
                'open_percent' => $this->percent($votesInOpenElections, $openCategoryCount),
                'positions_voted' => (int) $student->votes_count,
                'votes_in_open' => $votesInOpenElections,
                'open_categories' => $openCategoryCount,
                'last_vote_at' => $lastVote?->voted_at,
            ],
            'recentActivity' => $this->recentActivity($student),
            'donationSummary' => [
                'total_donated' => $totalDonated,
                'drives_supported' => $fundraisersSupported,
                'donations_made' => (int) $student->donations_count,
                'latest_title' => $lastDonation?->fundraiser?->title,
                'latest_amount' => $lastDonation ? (float) $lastDonation->amount : null,
                'latest_at' => $lastDonation?->donated_at,
                'history' => $recentDonations,
            ],
            'engagement' => [
                'voting' => $lifetimeVotingPercent,
                'events' => null,
                'competitions' => $this->percent($competitionsJoined, $eligibleCompetitions),
                'fundraising' => $this->percent($fundraisersSupported, $visibleFundraisers),
            ],
            'achievements' => [
                'most_active_month' => $this->mostActiveMonth($student),
                'total_activities' => $totalActivities,
                'supporter_level' => $this->supporterLevel($fundraisersSupported),
                'participation_level' => $this->participationLevel($totalActivities),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $joinedElectionIds
     */
    protected function eligibleElectionCount(User $student, Collection $joinedElectionIds): int
    {
        return (int) Election::query()
            ->whereNull('annulled_at')
            ->where(function ($query) use ($student, $joinedElectionIds) {
                if ($joinedElectionIds->isNotEmpty()) {
                    $query->whereIn('id', $joinedElectionIds);
                }

                $query->orWhere(function ($open) {
                    $open->acceptingVotes();
                })->orWhere(function ($duringMembership) use ($student) {
                    $duringMembership
                        ->whereIn('status', [ElectionStatus::Active, ElectionStatus::Closed])
                        ->whereNotNull('voting_starts_at')
                        ->where('voting_starts_at', '>=', $student->created_at);
                });
            })
            ->count();
    }

    /**
     * @param  Collection<int, int>  $joinedCompetitionIds
     */
    protected function eligibleCompetitionCount(User $student, Collection $joinedCompetitionIds): int
    {
        $published = TalentEvent::query()
            ->publishedToStudents()
            ->get([
                'id',
                'status',
                'published_to_students',
                'published_at',
                'created_at',
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

        return $published
            ->filter(function (TalentEvent $event) use ($student, $joinedCompetitionIds) {
                if ($joinedCompetitionIds->contains((int) $event->id)) {
                    return true;
                }

                $key = $event->currentStatusKey();
                if (in_array($key, ['registration_open', 'voting_open'], true)) {
                    return true;
                }

                $publishedAt = $event->published_at ?? $event->created_at;

                return $publishedAt && $publishedAt->gte($student->created_at);
            })
            ->count();
    }

    /**
     * @param  Collection<int, int>  $supportedFundraiserIds
     */
    protected function relevantFundraiserCount(Collection $supportedFundraiserIds): int
    {
        return (int) Fundraiser::query()
            ->where(function ($query) use ($supportedFundraiserIds) {
                $query->visibleToStudents();

                if ($supportedFundraiserIds->isNotEmpty()) {
                    $query->orWhereIn('id', $supportedFundraiserIds);
                }
            })
            ->count();
    }

    protected function participatingEntriesQuery(User $student)
    {
        return TalentEventEntry::query()
            ->where('user_id', $student->id)
            ->whereNotIn('status', [
                TalentEventEntry::STATUS_WITHDRAWN,
                TalentEventEntry::STATUS_ARCHIVED,
            ]);
    }

    /**
     * @return Collection<int, array{message: string, time: string}>
     */
    protected function recentActivity(User $student): Collection
    {
        $items = collect();

        Vote::query()
            ->with(['election:id,title'])
            ->where('user_id', $student->id)
            ->orderByDesc('voted_at')
            ->limit(8)
            ->get()
            ->unique('election_id')
            ->take(3)
            ->each(function (Vote $vote) use ($items) {
                $items->push([
                    'message' => 'Voted in '.($vote->election?->title ?? 'an election'),
                    'time' => $vote->voted_at?->diffForHumans() ?? 'Recently',
                    'at' => $vote->voted_at,
                ]);
            });

        $this->participatingEntriesQuery($student)
            ->with(['talentEvent:id,title'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->each(function (TalentEventEntry $entry) use ($items) {
                $items->push([
                    'message' => 'Joined '.($entry->talentEvent?->title ?? 'a talent competition'),
                    'time' => $entry->created_at?->diffForHumans() ?? 'Recently',
                    'at' => $entry->created_at,
                ]);
            });

        Donation::query()
            ->paid()
            ->with(['fundraiser:id,title'])
            ->where('user_id', $student->id)
            ->orderByDesc('donated_at')
            ->limit(3)
            ->get()
            ->each(function (Donation $donation) use ($items) {
                $amount = number_format((float) $donation->amount, 2);
                $title = $donation->fundraiser?->title ?? 'a fundraiser';
                $items->push([
                    'message' => 'Donated ₱'.$amount.' to '.$title,
                    'time' => $donation->donated_at?->diffForHumans() ?? 'Recently',
                    'at' => $donation->donated_at,
                ]);
            });

        $student->passkeys()
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->each(function ($passkey) use ($items) {
                $items->push([
                    'message' => 'Registered Passkey',
                    'time' => $passkey->created_at?->diffForHumans() ?? 'Recently',
                    'at' => $passkey->created_at,
                ]);
            });

        return $items
            ->sortByDesc(fn (array $item) => $item['at']?->getTimestamp() ?? 0)
            ->take(8)
            ->values()
            ->map(fn (array $item) => [
                'message' => $item['message'],
                'time' => $item['time'],
            ]);
    }

    protected function mostActiveMonth(User $student): ?string
    {
        $stamps = collect()
            ->merge(Vote::query()->where('user_id', $student->id)->pluck('voted_at'))
            ->merge($this->participatingEntriesQuery($student)->pluck('created_at'))
            ->merge(Donation::query()->paid()->where('user_id', $student->id)->pluck('donated_at'))
            ->filter()
            ->map(function ($value) {
                $date = $value instanceof Carbon ? $value : Carbon::parse($value);

                return $date->format('Y-m');
            });

        if ($stamps->isEmpty()) {
            return null;
        }

        $topMonth = $stamps
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return Carbon::createFromFormat('Y-m', $topMonth)?->format('F Y');
    }

    protected function supporterLevel(int $drivesSupported): string
    {
        return match (true) {
            $drivesSupported >= 4 => 'Campus champion',
            $drivesSupported >= 2 => 'Active donor',
            $drivesSupported === 1 => 'Supporter',
            default => 'Not yet donated',
        };
    }

    protected function participationLevel(int $totalActivities): string
    {
        return match (true) {
            $totalActivities >= 15 => 'Excellent',
            $totalActivities >= 8 => 'Very Active',
            $totalActivities >= 3 => 'Active',
            default => 'New Participant',
        };
    }

    protected function percent(int $part, int $whole): int
    {
        if ($whole <= 0) {
            return 0;
        }

        return min(100, (int) round(($part / $whole) * 100));
    }
}
