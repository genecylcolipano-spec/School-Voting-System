<?php

namespace App\Services\Student;

use App\Enums\ElectionStatus;
use App\Enums\EventStatus;
use App\Enums\FundraiserStatus;
use App\Enums\TalentEventStatus;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Election\StudentElectionService;
use App\Support\EventImageUrl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the student dashboard "Upcoming Activities" rows.
 *
 * Reuses existing model scopes and lifecycle helpers — no duplicated status logic.
 */
class StudentUpcomingActivitiesService
{
    public const DEFAULT_LIMIT = 15;

    /**
     * Lower number = higher list priority (then nearest schedule).
     */
    protected const PRIORITY = [
        'registration_open' => 1,
        'voting_open' => 2,
        'scheduled' => 3,
        'upcoming' => 3,
        'registration_period' => 3,
        'active' => 4,
        'ongoing' => 4,
        'results_pending' => 5,
        'voting_closed' => 5,
        'completed' => 6,
        'published' => 6,
        'results_published' => 6,
    ];

    public function __construct(
        protected StudentElectionService $electionService,
    ) {}

    /**
     * @return Collection<int, array{
     *     category: string,
     *     category_key: string,
     *     category_classes: string,
     *     title: string,
     *     banner_url: string,
     *     schedule_label: string,
     *     status_key: string,
     *     status_label: string,
     *     action_label: string,
     *     action_url: ?string,
     *     action_disabled: bool,
     *     action_style: string,
     *     sort_priority: int,
     *     sort_at: int
     * }>
     */
    public function forDashboard(?User $student = null, int $limit = self::DEFAULT_LIMIT): Collection
    {
        return $this->collect($student)
            ->sortBy([
                ['sort_priority', 'asc'],
                ['sort_at', 'asc'],
            ])
            ->take(max(1, $limit))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collect(?User $student = null): Collection
    {
        $items = collect();

        $this->pushElections($items, $student);
        $this->pushSchoolEvents($items);
        $this->pushTalentCompetitions($items);
        $this->pushFundraisers($items);

        return $items;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function pushElections(Collection $items, ?User $student): void
    {
        Election::query()
            ->whereNull('annulled_at')
            ->where(function ($query) {
                $query->where('status', ElectionStatus::Active)
                    ->orWhere(function ($inner) {
                        $inner->whereIn('status', [ElectionStatus::Closed, ElectionStatus::Archived])
                            ->where(function ($visible) {
                                $visible->where('public_results_published', true)
                                    ->orWhere(function ($recent) {
                                        $recent->whereNotNull('voting_ends_at')
                                            ->where('voting_ends_at', '>=', now()->subDays(30));
                                    });
                            });
                    });
            })
            ->orderBy('voting_starts_at')
            ->limit(20)
            ->get([
                'id',
                'title',
                'slug',
                'status',
                'is_paused',
                'voting_starts_at',
                'voting_ends_at',
                'public_results_published',
                'results_published_at',
                'annulled_at',
            ])
            ->each(function (Election $election) use ($items, $student) {
                $availability = $this->electionService->votingAvailability($election, $student);
                $mapped = $this->mapElectionAction($election, $availability);

                if ($mapped === null) {
                    return;
                }

                $sortAt = $election->voting_starts_at
                    ?? $election->voting_ends_at
                    ?? $election->results_published_at
                    ?? now();

                $items->push($this->row(
                    category: 'Election',
                    categoryKey: 'election',
                    title: $election->title,
                    bannerUrl: null,
                    scheduleLabel: $this->electionScheduleLabel($election),
                    statusKey: $mapped['status_key'],
                    statusLabel: $mapped['status_label'],
                    actionLabel: $mapped['action_label'],
                    actionUrl: $mapped['action_url'],
                    actionDisabled: $mapped['action_disabled'],
                    sortAt: $sortAt,
                ));
            });
    }

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
        return match ($availability['state']) {
            'open', 'voted' => [
                'status_key' => 'voting_open',
                'status_label' => 'Voting Open',
                'action_label' => $availability['can_vote'] ? 'Vote' : 'View',
                'action_url' => $availability['can_vote']
                    ? route('student.voting.show', $election)
                    : route('student.voting.index'),
                'action_disabled' => false,
            ],
            'results_published' => [
                'status_key' => 'published',
                'status_label' => 'Published',
                'action_label' => 'View Results',
                'action_url' => route('student.results.election.show', $election),
                'action_disabled' => false,
            ],
            'under_review' => [
                'status_key' => 'results_pending',
                'status_label' => 'Results Pending',
                'action_label' => 'Under Review',
                'action_url' => null,
                'action_disabled' => true,
            ],
            'not_started' => [
                'status_key' => 'scheduled',
                'status_label' => 'Scheduled',
                'action_label' => 'View',
                'action_url' => route('student.voting.index'),
                'action_disabled' => false,
            ],
            'paused' => [
                'status_key' => 'voting_closed',
                'status_label' => 'Voting Closed',
                'action_label' => 'View',
                'action_url' => route('student.voting.index'),
                'action_disabled' => false,
            ],
            default => null,
        };
    }

    protected function electionScheduleLabel(Election $election): string
    {
        if ($election->isAcceptingVotes() && $election->voting_ends_at) {
            return 'Ends '.$election->voting_ends_at->format('M d, Y');
        }

        if ($election->isBeforeVotingStart() && $election->voting_starts_at) {
            return 'Starts '.$election->voting_starts_at->format('M d, Y');
        }

        if ($election->voting_ends_at) {
            return $election->voting_ends_at->format('M d, Y');
        }

        if ($election->voting_starts_at) {
            return $election->voting_starts_at->format('M d, Y');
        }

        return 'TBA';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function pushSchoolEvents(Collection $items): void
    {
        Event::query()
            ->where('status', '!=', EventStatus::Cancelled)
            ->where(function ($query) {
                $query->where(function ($upcoming) {
                    $upcoming->where('status', EventStatus::Scheduled)
                        ->whereDate('event_date', '>=', now()->toDateString());
                })->orWhere(function ($completed) {
                    $completed->where('status', EventStatus::Completed)
                        ->where('event_date', '>=', now()->subDays(30));
                });
            })
            ->orderBy('event_date')
            ->limit(20)
            ->get(['id', 'title', 'slug', 'event_date', 'status', 'image_path', 'image_variants'])
            ->each(function (Event $event) use ($items) {
                $mapped = $this->mapSchoolEventAction($event);
                $sortAt = $event->event_date ?? now();

                $items->push($this->row(
                    category: 'School Event',
                    categoryKey: 'school_event',
                    title: $event->title,
                    bannerUrl: $event->has_uploaded_image
                        ? ($event->bannerThumbUrl() ?? $event->image_url)
                        : null,
                    scheduleLabel: $event->event_date?->format('M d, Y') ?? 'TBA',
                    statusKey: $mapped['status_key'],
                    statusLabel: $mapped['status_label'],
                    actionLabel: $mapped['action_label'],
                    actionUrl: $mapped['action_url'],
                    actionDisabled: false,
                    sortAt: $sortAt,
                ));
            });
    }

    /**
     * @return array{status_key: string, status_label: string, action_label: string, action_url: string}
     */
    protected function mapSchoolEventAction(Event $event): array
    {
        $showUrl = route('student.events.show', $event);

        if ($event->status === EventStatus::Completed) {
            return [
                'status_key' => 'completed',
                'status_label' => 'Completed',
                'action_label' => 'View Summary',
                'action_url' => $showUrl,
            ];
        }

        if ($event->event_date && $event->event_date->isToday()) {
            return [
                'status_key' => 'active',
                'status_label' => 'Ongoing',
                'action_label' => 'Join',
                'action_url' => $showUrl,
            ];
        }

        return [
            'status_key' => 'upcoming',
            'status_label' => 'Upcoming',
            'action_label' => 'View',
            'action_url' => $showUrl,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function pushTalentCompetitions(Collection $items): void
    {
        TalentEvent::query()
            ->publishedToStudents()
            ->where(function ($query) {
                $query->where('status', '!=', TalentEventStatus::Completed)
                    ->orWhereNotNull('results_published_at');
            })
            ->where(function ($query) {
                $query->whereNull('results_published_at')
                    ->orWhere('results_published_at', '>=', now()->subDays(30));
            })
            ->orderBy('event_date')
            ->limit(20)
            ->get()
            ->each(function (TalentEvent $talent) use ($items) {
                if ($talent->isArchived() && ! $talent->hasPublishedResults()) {
                    return;
                }

                $phase = $talent->currentStatus();
                $mapped = $this->mapTalentAction($talent, $phase);

                if ($mapped === null) {
                    return;
                }

                $sortAt = $talent->event_date
                    ?? $talent->registration_ends_at
                    ?? $talent->voting_ends_at
                    ?? $talent->voting_starts_at
                    ?? now();

                $items->push($this->row(
                    category: 'Talent Competition',
                    categoryKey: 'talent',
                    title: $talent->title,
                    bannerUrl: $talent->has_uploaded_image
                        ? ($talent->bannerThumbUrl() ?? $talent->image_url)
                        : null,
                    scheduleLabel: $this->talentScheduleLabel($talent, $phase['key']),
                    statusKey: $mapped['status_key'],
                    statusLabel: $mapped['status_label'],
                    actionLabel: $mapped['action_label'],
                    actionUrl: $mapped['action_url'],
                    actionDisabled: $mapped['action_disabled'],
                    sortAt: $sortAt instanceof Carbon ? $sortAt : Carbon::parse($sortAt),
                ));
            });
    }

    /**
     * @param  array{key: string, label: string}  $phase
     * @return array{status_key: string, status_label: string, action_label: string, action_url: ?string, action_disabled: bool}|null
     */
    protected function mapTalentAction(TalentEvent $talent, array $phase): ?array
    {
        $key = $phase['key'];
        $label = $phase['label'];

        return match ($key) {
            'registration_open' => [
                'status_key' => 'registration_open',
                'status_label' => $label,
                'action_label' => 'View Details',
                'action_url' => route('student.talent-registration.show', $talent),
                'action_disabled' => false,
            ],
            'registration_closed' => [
                'status_key' => 'registration_closed',
                'status_label' => $label,
                'action_label' => 'View Details',
                'action_url' => route('student.talent-registration.show', $talent),
                'action_disabled' => false,
            ],
            'voting_open' => [
                'status_key' => 'voting_open',
                'status_label' => $label,
                'action_label' => 'Vote',
                'action_url' => route('student.talent-voting.show', $talent),
                'action_disabled' => false,
            ],
            'voting_closed', 'voting_paused' => [
                'status_key' => 'results_pending',
                'status_label' => 'Results Pending',
                'action_label' => 'Under Review',
                'action_url' => null,
                'action_disabled' => true,
            ],
            'results_published' => [
                'status_key' => 'published',
                'status_label' => 'Published',
                'action_label' => 'View Results',
                'action_url' => route('student.results.talent.show', $talent),
                'action_disabled' => false,
            ],
            'scheduled' => [
                'status_key' => 'scheduled',
                'status_label' => $label,
                'action_label' => 'View Details',
                'action_url' => route('student.talent-registration.show', $talent),
                'action_disabled' => false,
            ],
            'archived' => $talent->hasPublishedResults() ? [
                'status_key' => 'completed',
                'status_label' => 'Completed',
                'action_label' => 'View Results',
                'action_url' => route('student.results.talent.show', $talent),
                'action_disabled' => false,
            ] : null,
            default => [
                'status_key' => $key,
                'status_label' => $label,
                'action_label' => 'View Details',
                'action_url' => route('student.talent-registration.show', $talent),
                'action_disabled' => false,
            ],
        };
    }

    protected function talentScheduleLabel(TalentEvent $talent, string $phaseKey): string
    {
        return match ($phaseKey) {
            'registration_open' => $talent->registration_ends_at
                ? 'Register by '.$talent->registration_ends_at->format('M d, Y')
                : ($talent->event_date?->format('M d, Y') ?? 'Registration Open'),
            'voting_open' => $talent->voting_ends_at
                ? 'Voting ends '.$talent->voting_ends_at->format('M d, Y')
                : ($talent->event_date?->format('M d, Y') ?? 'Voting Open'),
            default => $talent->event_date?->format('M d, Y')
                ?? ($talent->voting_ends_at?->format('M d, Y') ?? 'TBA'),
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function pushFundraisers(Collection $items): void
    {
        Fundraiser::query()
            ->visibleToStudents()
            ->where(function ($query) {
                $query->where(function ($active) {
                    $active->where('accept_donations', true)
                        ->where(function ($window) {
                            $window->whereNull('ends_on')
                                ->orWhereDate('ends_on', '>=', now());
                        });
                })->orWhere(function ($ended) {
                    $ended->whereIn('status', [
                        FundraiserStatus::Completed->value,
                        FundraiserStatus::GoalReached->value,
                        FundraiserStatus::Active->value,
                    ])->whereNotNull('ends_on')
                        ->whereDate('ends_on', '>=', now()->subDays(30)->toDateString())
                        ->whereDate('ends_on', '<', now()->toDateString());
                })->orWhere(function ($scheduled) {
                    $scheduled->whereNotNull('starts_on')
                        ->whereDate('starts_on', '>', now());
                });
            })
            ->orderBy('ends_on')
            ->limit(20)
            ->get([
                'id',
                'title',
                'slug',
                'status',
                'visibility',
                'accept_donations',
                'starts_on',
                'ends_on',
                'goal_amount',
                'amount_raised',
                'banner_path',
                'banner_variants',
            ])
            ->each(function (Fundraiser $fundraiser) use ($items) {
                $resolved = $fundraiser->resolvedStatus();
                $accepting = $fundraiser->isAcceptingDonations();
                $showUrl = route('student.fundraising.show', $fundraiser);

                if ($accepting) {
                    $statusKey = 'active';
                    $statusLabel = 'Active';
                    $actionLabel = 'Donate';
                    $actionUrl = $showUrl;
                    $priorityKey = 'active';
                } elseif (in_array($resolved, [FundraiserStatus::Completed, FundraiserStatus::GoalReached], true)
                    || ($fundraiser->ends_on && $fundraiser->ends_on->lt(now()->startOfDay()))) {
                    $statusKey = 'completed';
                    $statusLabel = 'Completed';
                    $actionLabel = 'View Campaign';
                    $actionUrl = $showUrl;
                    $priorityKey = 'completed';
                } else {
                    $statusKey = 'scheduled';
                    $statusLabel = $fundraiser->displayStatusLabel();
                    $actionLabel = 'View Campaign';
                    $actionUrl = $showUrl;
                    $priorityKey = 'scheduled';
                }

                $sortAt = $fundraiser->ends_on
                    ? Carbon::parse($fundraiser->ends_on)->startOfDay()
                    : ($fundraiser->starts_on
                        ? Carbon::parse($fundraiser->starts_on)->startOfDay()
                        : now());

                $banner = $fundraiser->hasUploadedBanner()
                    ? ($fundraiser->bannerMediumUrl() ?? $fundraiser->bannerUrl())
                    : null;

                $items->push($this->row(
                    category: 'Fundraising',
                    categoryKey: 'fundraising',
                    title: $fundraiser->title,
                    bannerUrl: $banner,
                    scheduleLabel: $fundraiser->ends_on
                        ? 'Ends '.$fundraiser->ends_on->format('M d, Y')
                        : ($fundraiser->starts_on
                            ? 'Starts '.$fundraiser->starts_on->format('M d, Y')
                            : 'Open now'),
                    statusKey: $statusKey,
                    statusLabel: $statusLabel,
                    actionLabel: $actionLabel,
                    actionUrl: $actionUrl,
                    actionDisabled: false,
                    sortAt: $sortAt,
                    priorityKey: $priorityKey,
                ));
            });
    }

    /**
     * @return array{
     *     category: string,
     *     category_key: string,
     *     category_classes: string,
     *     title: string,
     *     banner_url: string,
     *     schedule_label: string,
     *     status_key: string,
     *     status_label: string,
     *     action_label: string,
     *     action_url: ?string,
     *     action_disabled: bool,
     *     action_style: string,
     *     sort_priority: int,
     *     sort_at: int
     * }
     */
    protected function row(
        string $category,
        string $categoryKey,
        string $title,
        ?string $bannerUrl,
        string $scheduleLabel,
        string $statusKey,
        string $statusLabel,
        string $actionLabel,
        ?string $actionUrl,
        bool $actionDisabled,
        Carbon|int $sortAt,
        ?string $priorityKey = null,
    ): array {
        $timestamp = $sortAt instanceof Carbon ? $sortAt->getTimestamp() : (int) $sortAt;
        $priorityLookup = $priorityKey ?? $statusKey;

        return [
            'category' => $category,
            'category_key' => $categoryKey,
            'category_classes' => $this->categoryClasses($categoryKey),
            'title' => $title,
            'banner_url' => EventImageUrl::uploadedOrCover($bannerUrl, $categoryKey),
            'schedule_label' => $scheduleLabel,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'action_label' => $actionLabel,
            'action_url' => $actionDisabled ? null : $actionUrl,
            'action_disabled' => $actionDisabled,
            'action_style' => $this->actionStyle($actionLabel, $actionDisabled),
            'sort_priority' => self::PRIORITY[$priorityLookup] ?? 99,
            'sort_at' => $timestamp,
        ];
    }

    /**
     * primary = high-intent CTA, secondary = browse/view, disabled = non-interactive.
     */
    protected function actionStyle(string $actionLabel, bool $actionDisabled): string
    {
        if ($actionDisabled) {
            return 'disabled';
        }

        return match ($actionLabel) {
            'Vote', 'Donate', 'Join' => 'primary',
            default => 'secondary',
        };
    }

    protected function categoryClasses(string $categoryKey): string
    {
        return match ($categoryKey) {
            'election' => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
            'school_event' => 'border-sky-500/30 bg-sky-500/15 text-sky-300',
            'talent' => 'border-violet-500/30 bg-violet-500/15 text-violet-300',
            'fundraising' => 'border-amber-500/30 bg-amber-500/15 text-amber-300',
            default => 'border-cyan-500/30 bg-cyan-500/15 text-cyan-300',
        };
    }
}
